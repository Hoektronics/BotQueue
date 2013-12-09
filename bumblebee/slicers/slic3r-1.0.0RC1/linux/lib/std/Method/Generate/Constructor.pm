package Method::Generate::Constructor;

use strictures 1;
use Sub::Quote;
use base qw(Moo::Object);
use Sub::Defer;
use B 'perlstring';
use Moo::_Utils qw(_getstash);

sub register_attribute_specs {
  my ($self, @new_specs) = @_;
  my $specs = $self->{attribute_specs}||={};
  while (my ($name, $new_spec) = splice @new_specs, 0, 2) {
    if ($name =~ s/^\+//) {
      die "has '+${name}' given but no ${name} attribute already exists"
        unless my $old_spec = $specs->{$name};
      foreach my $key (keys %$old_spec) {
        if (!exists $new_spec->{$key}) {
          $new_spec->{$key} = $old_spec->{$key}
            unless $key eq 'handles';
        }
        elsif ($key eq 'moosify') {
          $new_spec->{$key} = [
            map { ref $_ eq 'ARRAY' ? @$_ : $_ }
              ($old_spec->{$key}, $new_spec->{$key})
          ];
        }
      }
    }
    $new_spec->{index} = scalar keys %$specs
      unless defined $new_spec->{index};
    $specs->{$name} = $new_spec;
  }
  $self;
}

sub all_attribute_specs {
  $_[0]->{attribute_specs}
}

sub accessor_generator {
  $_[0]->{accessor_generator}
}

sub construction_string {
  my ($self) = @_;
  $self->{construction_string}
    ||= $self->_build_construction_string;
}

sub _build_construction_string {
  'bless('
    .$_[0]->accessor_generator->default_construction_string
    .', $class);'
}

sub install_delayed {
  my ($self) = @_;
  my $package = $self->{package};
  defer_sub "${package}::new" => sub {
    unquote_sub $self->generate_method(
      $package, 'new', $self->{attribute_specs}, { no_install => 1 }
    )
  };
  $self;
}

sub generate_method {
  my ($self, $into, $name, $spec, $quote_opts) = @_;
  foreach my $no_init (grep !exists($spec->{$_}{init_arg}), keys %$spec) {
    $spec->{$no_init}{init_arg} = $no_init;
  }
  local $self->{captures} = {};
  my $body = '    my $class = shift;'."\n"
            .'    $class = ref($class) if ref($class);'."\n";
  $body .= $self->_handle_subconstructor($into, $name);
  my $into_buildargs = $into->can('BUILDARGS');
  if ( $into_buildargs && $into_buildargs != \&Moo::Object::BUILDARGS ) {
      $body .= $self->_generate_args_via_buildargs;
  } else {
      $body .= $self->_generate_args;
  }
  $body .= $self->_check_required($spec);
  $body .= '    my $new = '.$self->construction_string.";\n";
  $body .= $self->_assign_new($spec);
  if ($into->can('BUILD')) {
    require Method::Generate::BuildAll;
    $body .= Method::Generate::BuildAll->new->buildall_body_for(
      $into, '$new', '$args'
    );
  }
  $body .= '    return $new;'."\n";
  if ($into->can('DEMOLISH')) {
    require Method::Generate::DemolishAll;
    Method::Generate::DemolishAll->new->generate_method($into);
  }
  quote_sub
    "${into}::${name}" => $body,
    $self->{captures}, $quote_opts||{}
  ;
}

sub _handle_subconstructor {
  my ($self, $into, $name) = @_;
  if (my $gen = $self->{subconstructor_handler}) {
    '    if ($class ne '.perlstring($into).') {'."\n".
    $gen.
    '    }'."\n";
  } else {
    ''
  }
}

sub _cap_call {
  my ($self, $code, $captures) = @_;
  @{$self->{captures}}{keys %$captures} = values %$captures if $captures;
  $code;
}

sub _generate_args_via_buildargs {
  my ($self) = @_;
  q{    my $args = $class->BUILDARGS(@_);}."\n"
  .q{    die "BUILDARGS did not return a hashref" unless ref($args) eq 'HASH';}
  ."\n";
}

# inlined from Moo::Object - update that first.
sub _generate_args {
  my ($self) = @_;
  return <<'_EOA';
    my $args;
    if ( scalar @_ == 1 ) {
        unless ( defined $_[0] && ref $_[0] eq 'HASH' ) {
            die "Single parameters to new() must be a HASH ref"
                ." data => ". $_[0] ."\n";
        }
        $args = { %{ $_[0] } };
    }
    elsif ( @_ % 2 ) {
        die "The new() method for $class expects a hash reference or a key/value list."
                . " You passed an odd number of arguments\n";
    }
    else {
        $args = {@_};
    }
_EOA

}

sub _assign_new {
  my ($self, $spec) = @_;
  my $ag = $self->accessor_generator;
  my %test;
  NAME: foreach my $name (sort keys %$spec) {
    my $attr_spec = $spec->{$name};
    next NAME unless defined($attr_spec->{init_arg})
                       or $ag->has_eager_default($name, $attr_spec);
    $test{$name} = $attr_spec->{init_arg};
  }
  join '', map {
    my $arg_key = perlstring($test{$_});
    my $test = "exists \$args->{$arg_key}";
    my $source = "\$args->{$arg_key}";
    my $attr_spec = $spec->{$_};
    $self->_cap_call($ag->generate_populate_set(
      '$new', $_, $attr_spec, $source, $test, $test{$_},
    ));
  } sort keys %test;
}

sub _check_required {
  my ($self, $spec) = @_;
  my @required_init =
    map $spec->{$_}{init_arg},
      grep {
        my %s = %{$spec->{$_}}; # ignore required if default or builder set
        $s{required} and not($s{builder} or $s{default})
      } sort keys %$spec;
  return '' unless @required_init;
  '    if (my @missing = grep !exists $args->{$_}, qw('
    .join(' ',@required_init).')) {'."\n"
    .q{      die "Missing required arguments: ".join(', ', sort @missing);}."\n"
    ."    }\n";
}

use Moo;
Moo->_constructor_maker_for(__PACKAGE__)->register_attribute_specs(
  attribute_specs => {
    is => 'ro',
    reader => 'all_attribute_specs',
  },
  accessor_generator => { is => 'ro' },
  construction_string => { is => 'lazy' },
  subconstructor_handler => { is => 'ro' },
  package => { is => 'ro' },
);

1;
