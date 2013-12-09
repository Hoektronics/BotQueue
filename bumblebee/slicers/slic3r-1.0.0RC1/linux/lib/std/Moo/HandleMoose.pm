package Moo::HandleMoose;

use strictures 1;
use Moo::_Utils;
use B qw(perlstring);

our %TYPE_MAP;

our $SETUP_DONE;

sub import { return if $SETUP_DONE; inject_all(); $SETUP_DONE = 1; }

sub inject_all {
  require Class::MOP;
  inject_fake_metaclass_for($_)
    for grep $_ ne 'Moo::Object', do { no warnings 'once'; keys %Moo::MAKERS };
  inject_fake_metaclass_for($_) for keys %Moo::Role::INFO;
  require Moose::Meta::Method::Constructor;
  @Moo::HandleMoose::FakeConstructor::ISA = 'Moose::Meta::Method::Constructor';
}

sub maybe_reinject_fake_metaclass_for {
  my ($name) = @_;
  our %DID_INJECT;
  if (delete $DID_INJECT{$name}) {
    unless ($Moo::Role::INFO{$name}) {
      Moo->_constructor_maker_for($name)->install_delayed;
    }
    inject_fake_metaclass_for($name);
  }
}

sub inject_fake_metaclass_for {
  my ($name) = @_;
  require Class::MOP;
  require Moo::HandleMoose::FakeMetaClass;
  Class::MOP::store_metaclass_by_name(
    $name, bless({ name => $name }, 'Moo::HandleMoose::FakeMetaClass')
  );
  require Moose::Util::TypeConstraints;
  if ($Moo::Role::INFO{$name}) {
    Moose::Util::TypeConstraints::find_or_create_does_type_constraint($name);
  } else {
    Moose::Util::TypeConstraints::find_or_create_isa_type_constraint($name);
  }
}

{
  package Moo::HandleMoose::FakeConstructor;

  sub _uninlined_body { \&Moose::Object::new }
}
    

sub inject_real_metaclass_for {
  my ($name) = @_;
  our %DID_INJECT;
  return Class::MOP::get_metaclass_by_name($name) if $DID_INJECT{$name};
  require Moose; require Moo; require Moo::Role; require Scalar::Util;
  Class::MOP::remove_metaclass_by_name($name);
  my ($am_role, $meta, $attr_specs, $attr_order) = do {
    if (my $info = $Moo::Role::INFO{$name}) {
      my @attr_info = @{$info->{attributes}||[]};
      (1, Moose::Meta::Role->initialize($name),
       { @attr_info },
       [ @attr_info[grep !($_ % 2), 0..$#attr_info] ]
      )
    } elsif ( my $cmaker = Moo->_constructor_maker_for($name) ) {
      my $specs = $cmaker->all_attribute_specs;
      (0, Moose::Meta::Class->initialize($name), $specs,
       [ sort { $specs->{$a}{index} <=> $specs->{$b}{index} } keys %$specs ]
      );
    } else {
       # This codepath is used if $name does not exist in $Moo::MAKERS
       (0, Moose::Meta::Class->initialize($name), {}, [] )
    }
  };

  for my $spec (values %$attr_specs) {
    if (my $inflators = delete $spec->{moosify}) {
      $_->($spec) for @$inflators;
    }
  }

  my %methods = %{Role::Tiny->_concrete_methods_of($name)};

  # if stuff gets added afterwards, _maybe_reset_handlemoose should
  # trigger the recreation of the metaclass but we need to ensure the
  # Role::Tiny cache is cleared so we don't confuse Moo itself.
  if (my $info = $Role::Tiny::INFO{$name}) {
    delete $info->{methods};
  }

  # needed to ensure the method body is stable and get things named
  Sub::Defer::undefer_sub($_) for grep defined, values %methods;
  my @attrs;
  {
    # This local is completely not required for roles but harmless
    local @{_getstash($name)}{keys %methods};
    my %seen_name;
    foreach my $name (@$attr_order) {
      $seen_name{$name} = 1;
      my %spec = %{$attr_specs->{$name}};
      my %spec_map = (
        map { $_->name => $_->init_arg||$_->name }
        (
          (grep { $_->has_init_arg }
             $meta->attribute_metaclass->meta->get_all_attributes),
          grep { exists($_->{init_arg}) ? defined($_->init_arg) : 1 }
          map {
            my $meta = Moose::Util::resolve_metatrait_alias('Attribute', $_)
                         ->meta;
            map $meta->get_attribute($_), $meta->get_attribute_list
          }  @{$spec{traits}||[]}
        )
      );
      # have to hard code this because Moose's role meta-model is lacking
      $spec_map{traits} ||= 'traits';

      $spec{is} = 'ro' if $spec{is} eq 'lazy' or $spec{is} eq 'rwp';
      my $coerce = $spec{coerce};
      if (my $isa = $spec{isa}) {
        my $tc = $spec{isa} = do {
          if (my $mapped = $TYPE_MAP{$isa}) {
            my $type = $mapped->();
            Scalar::Util::blessed($type) && $type->isa("Moose::Meta::TypeConstraint")
              or die "error inflating attribute '$name' for package '$_[0]': \$TYPE_MAP{$isa} did not return a valid type constraint'";
            $coerce ? $type->create_child_type(name => $type->name) : $type;
          } else {
            Moose::Meta::TypeConstraint->new(
              constraint => sub { eval { &$isa; 1 } }
            );
          }
        };
        if ($coerce) {
          $tc->coercion(Moose::Meta::TypeCoercion->new)
             ->_compiled_type_coercion($coerce);
          $spec{coerce} = 1;
        }
      } elsif ($coerce) {
        my $attr = perlstring($name);
        my $tc = Moose::Meta::TypeConstraint->new(
                   constraint => sub { die "This is not going to work" },
                   inlined => sub {
                      'my $r = $_[42]{'.$attr.'}; $_[42]{'.$attr.'} = 1; $r'
                   },
                 );
        $tc->coercion(Moose::Meta::TypeCoercion->new)
           ->_compiled_type_coercion($coerce);
        $spec{isa} = $tc;
        $spec{coerce} = 1;
      }
      %spec =
        map { $spec_map{$_} => $spec{$_} }
        grep { exists $spec_map{$_} }
        keys %spec;
      push @attrs, $meta->add_attribute($name => %spec);
    }
    foreach my $mouse (do { our %MOUSE; @{$MOUSE{$name}||[]} }) {
      foreach my $attr ($mouse->get_all_attributes) {
        my %spec = %{$attr};
        delete @spec{qw(
          associated_class associated_methods __METACLASS__
          provides curries
        )};
        my $name = delete $spec{name};
        next if $seen_name{$name}++;
        push @attrs, $meta->add_attribute($name => %spec);
      }
    }
  }
  for my $meth_name (keys %methods) {
    my $meth_code = $methods{$meth_name};
    $meta->add_method($meth_name, $meth_code) if $meth_code;
  }

  if ($am_role) {
    my $info = $Moo::Role::INFO{$name};
    $meta->add_required_methods(@{$info->{requires}});
    foreach my $modifier (@{$info->{modifiers}}) {
      my ($type, @args) = @$modifier;
      my $code = pop @args;
      $meta->${\"add_${type}_method_modifier"}($_, $code) for @args;
    }
  } else {
    foreach my $attr (@attrs) {
      foreach my $method (@{$attr->associated_methods}) {
        $method->{body} = $name->can($method->name);
      }
    }
    bless(
      $meta->find_method_by_name('new'),
      'Moo::HandleMoose::FakeConstructor',
    );
    # a combination of Moo and Moose may bypass a Moo constructor but still
    # use a Moo DEMOLISHALL.  We need to make sure this is loaded before
    # global destruction.
    require Method::Generate::DemolishAll;
  }
  $meta->add_role(Class::MOP::class_of($_))
    for grep !/\|/ && $_ ne $name, # reject Foo|Bar and same-role-as-self
      do { no warnings 'once'; keys %{$Role::Tiny::APPLIED_TO{$name}} };
  $DID_INJECT{$name} = 1;
  $meta;
}

1;
