package Moo;

use strictures 1;
use Moo::_Utils;
use B 'perlstring';
use Sub::Defer ();

our $VERSION = '1.003001';
$VERSION = eval $VERSION;

require Moo::sification;

our %MAKERS;

sub _install_tracked {
  my ($target, $name, $code) = @_;
  $MAKERS{$target}{exports}{$name} = $code;
  _install_coderef "${target}::${name}" => "Moo::${name}" => $code;
}

sub import {
  my $target = caller;
  my $class = shift;
  strictures->import;
  if ($Role::Tiny::INFO{$target} and $Role::Tiny::INFO{$target}{is_role}) {
    die "Cannot import Moo into a role";
  }
  $MAKERS{$target} ||= {};
  _install_tracked $target => extends => sub {
    $class->_set_superclasses($target, @_);
    $class->_maybe_reset_handlemoose($target);
    return;
  };
  _install_tracked $target => with => sub {
    require Moo::Role;
    Moo::Role->apply_roles_to_package($target, @_);
    $class->_maybe_reset_handlemoose($target);
  };
  _install_tracked $target => has => sub {
    my $name_proto = shift;
    my @name_proto = ref $name_proto eq 'ARRAY' ? @$name_proto : $name_proto;
    if (@_ % 2 != 0) {
      require Carp;
      Carp::croak("Invalid options for " . join(', ', map "'$_'", @name_proto)
        . " attribute(s): even number of arguments expected, got " . scalar @_)
    }
    my %spec = @_;
    foreach my $name (@name_proto) {
      # Note that when multiple attributes specified, each attribute
      # needs a separate \%specs hashref
      my $spec_ref = @name_proto > 1 ? +{%spec} : \%spec;
      $class->_constructor_maker_for($target)
            ->register_attribute_specs($name, $spec_ref);
      $class->_accessor_maker_for($target)
            ->generate_method($target, $name, $spec_ref);
      $class->_maybe_reset_handlemoose($target);
    }
    return;
  };
  foreach my $type (qw(before after around)) {
    _install_tracked $target => $type => sub {
      require Class::Method::Modifiers;
      _install_modifier($target, $type, @_);
      return;
    };
  }
  return if $MAKERS{$target}{is_class}; # already exported into this package
  $MAKERS{$target}{is_class} = 1;
  {
    no strict 'refs';
    @{"${target}::ISA"} = do {
      require Moo::Object; ('Moo::Object');
    } unless @{"${target}::ISA"};
  }
  if ($INC{'Moo/HandleMoose.pm'}) {
    Moo::HandleMoose::inject_fake_metaclass_for($target);
  }
}

sub unimport {
  my $target = caller;
  _unimport_coderefs($target, $MAKERS{$target});
}

sub _set_superclasses {
  my $class = shift;
  my $target = shift;
  foreach my $superclass (@_) {
    _load_module($superclass);
    if ($INC{"Role/Tiny.pm"} && $Role::Tiny::INFO{$superclass}) {
      require Carp;
      Carp::croak("Can't extend role '$superclass'");
    }
  }
  # Can't do *{...} = \@_ or 5.10.0's mro.pm stops seeing @ISA
  @{*{_getglob("${target}::ISA")}{ARRAY}} = @_;
  if (my $old = delete $Moo::MAKERS{$target}{constructor}) {
    delete _getstash($target)->{new};
    Moo->_constructor_maker_for($target)
       ->register_attribute_specs(%{$old->all_attribute_specs});
  }
  elsif (!$target->isa('Moo::Object')) {
    Moo->_constructor_maker_for($target);
  }
  no warnings 'once'; # piss off. -- mst
  $Moo::HandleMoose::MOUSE{$target} = [
    grep defined, map Mouse::Util::find_meta($_), @_
  ] if Mouse::Util->can('find_meta');
}

sub _maybe_reset_handlemoose {
  my ($class, $target) = @_;
  if ($INC{"Moo/HandleMoose.pm"}) {
    Moo::HandleMoose::maybe_reinject_fake_metaclass_for($target);
  }
}

sub _accessor_maker_for {
  my ($class, $target) = @_;
  return unless $MAKERS{$target};
  $MAKERS{$target}{accessor} ||= do {
    my $maker_class = do {
      if (my $m = do {
            if (my $defer_target = 
                  (Sub::Defer::defer_info($target->can('new'))||[])->[0]
              ) {
              my ($pkg) = ($defer_target =~ /^(.*)::[^:]+$/);
              $MAKERS{$pkg} && $MAKERS{$pkg}{accessor};
            } else {
              undef;
            }
          }) {
        ref($m);
      } else {
        require Method::Generate::Accessor;
        'Method::Generate::Accessor'
      }
    };
    $maker_class->new;
  }
}

sub _constructor_maker_for {
  my ($class, $target, $select_super) = @_;
  return unless $MAKERS{$target};
  $MAKERS{$target}{constructor} ||= do {
    require Method::Generate::Constructor;
    require Sub::Defer;
    my ($moo_constructor, $con);

    if ($select_super && $MAKERS{$select_super}) {
      $moo_constructor = 1;
      $con = $MAKERS{$select_super}{constructor};
    } else {
      my $t_new = $target->can('new');
      if ($t_new) {
        if ($t_new == Moo::Object->can('new')) {
          $moo_constructor = 1;
        } elsif (my $defer_target = (Sub::Defer::defer_info($t_new)||[])->[0]) {
          my ($pkg) = ($defer_target =~ /^(.*)::[^:]+$/);
          if ($MAKERS{$pkg}) {
            $moo_constructor = 1;
            $con = $MAKERS{$pkg}{constructor};
          }
        }
      } else {
        $moo_constructor = 1; # no other constructor, make a Moo one
      }
    };
    ($con ? ref($con) : 'Method::Generate::Constructor')
      ->new(
        package => $target,
        accessor_generator => $class->_accessor_maker_for($target),
        construction_string => (
          $moo_constructor
            ? ($con ? $con->construction_string : undef)
            : ('$class->'.$target.'::SUPER::new($class->can(q[FOREIGNBUILDARGS]) ? $class->FOREIGNBUILDARGS(@_) : @_)')
        ),
        subconstructor_handler => (
          '      if ($Moo::MAKERS{$class}) {'."\n"
          .'        '.$class.'->_constructor_maker_for($class,'.perlstring($target).');'."\n"
          .'        return $class->new(@_)'.";\n"
          .'      } elsif ($INC{"Moose.pm"} and my $meta = Class::MOP::get_metaclass_by_name($class)) {'."\n"
          .'        return $meta->new_object($class->BUILDARGS(@_));'."\n"
          .'      }'."\n"
        ),
      )
      ->install_delayed
      ->register_attribute_specs(%{$con?$con->all_attribute_specs:{}})
  }
}

1;
=pod

=encoding utf-8

=head1 NAME

Moo - Minimalist Object Orientation (with Moose compatibility)

=head1 SYNOPSIS

 package Cat::Food;

 use Moo;

 sub feed_lion {
   my $self = shift;
   my $amount = shift || 1;

   $self->pounds( $self->pounds - $amount );
 }

 has taste => (
   is => 'ro',
 );

 has brand => (
   is  => 'ro',
   isa => sub {
     die "Only SWEET-TREATZ supported!" unless $_[0] eq 'SWEET-TREATZ'
   },
 );

 has pounds => (
   is  => 'rw',
   isa => sub { die "$_[0] is too much cat food!" unless $_[0] < 15 },
 );

 1;

And elsewhere:

 my $full = Cat::Food->new(
    taste  => 'DELICIOUS.',
    brand  => 'SWEET-TREATZ',
    pounds => 10,
 );

 $full->feed_lion;

 say $full->pounds;

=head1 DESCRIPTION

This module is an extremely light-weight subset of L<Moose> optimised for
rapid startup and "pay only for what you use".

It also avoids depending on any XS modules to allow simple deployments.  The
name C<Moo> is based on the idea that it provides almost -- but not quite -- two
thirds of L<Moose>.

Unlike L<Mouse> this module does not aim at full compatibility with
L<Moose>'s surface syntax, preferring instead of provide full interoperability
via the metaclass inflation capabilities described in L</MOO AND MOOSE>.

For a full list of the minor differences between L<Moose> and L<Moo>'s surface
syntax, see L</INCOMPATIBILITIES WITH MOOSE>.

=head1 WHY MOO EXISTS

If you want a full object system with a rich Metaprotocol, L<Moose> is
already wonderful.

However, sometimes you're writing a command line script or a CGI script
where fast startup is essential, or code designed to be deployed as a single
file via L<App::FatPacker>, or you're writing a CPAN module and you want it
to be usable by people with those constraints.

I've tried several times to use L<Mouse> but it's 3x the size of Moo and
takes longer to load than most of my Moo based CGI scripts take to run.

If you don't want L<Moose>, you don't want "less metaprotocol" like L<Mouse>,
you want "as little as possible" -- which means "no metaprotocol", which is
what Moo provides.

Better still, if you install and load L<Moose>, we set up metaclasses for your
L<Moo> classes and L<Moo::Role> roles, so you can use them in L<Moose> code
without ever noticing that some of your codebase is using L<Moo>.

Hence, Moo exists as its name -- Minimal Object Orientation -- with a pledge
to make it smooth to upgrade to L<Moose> when you need more than minimal
features.

=head1 MOO AND MOOSE

If L<Moo> detects L<Moose> being loaded, it will automatically register
metaclasses for your L<Moo> and L<Moo::Role> packages, so you should be able
to use them in L<Moose> code without anybody ever noticing you aren't using
L<Moose> everywhere.

L<Moo> will also create L<Moose type constraints|Moose::Manual::Types> for
classes and roles, so that C<< isa => 'MyClass' >> and C<< isa => 'MyRole' >>
work the same as for L<Moose> classes and roles.

Extending a L<Moose> class or consuming a L<Moose::Role> will also work.

So will extending a L<Mouse> class or consuming a L<Mouse::Role> - but note
that we don't provide L<Mouse> metaclasses or metaroles so the other way
around doesn't work. This feature exists for L<Any::Moose> users porting to
L<Moo>; enabling L<Mouse> users to use L<Moo> classes is not a priority for us.

This means that there is no need for anything like L<Any::Moose> for Moo
code - Moo and Moose code should simply interoperate without problem. To
handle L<Mouse> code, you'll likely need an empty Moo role or class consuming
or extending the L<Mouse> stuff since it doesn't register true L<Moose>
metaclasses like L<Moo> does.

If you want types to be upgraded to the L<Moose> types, use
L<MooX::Types::MooseLike> and install the L<MooseX::Types> library to
match the L<MooX::Types::MooseLike> library you're using - L<Moo> will
load the L<MooseX::Types> library and use that type for the newly created
metaclass.

If you need to disable the metaclass creation, add:

  no Moo::sification;

to your code before Moose is loaded, but bear in mind that this switch is
currently global and turns the mechanism off entirely so don't put this
in library code.

=head1 MOO AND CLASS::XSACCESSOR

If a new enough version of L<Class::XSAccessor> is available, it
will be used to generate simple accessors, readers, and writers for
a speed boost.  Simple accessors are those without lazy defaults,
type checks/coercions, or triggers.  Readers and writers generated
by L<Class::XSAccessor> will behave slightly differently: they will
reject attempts to call them with the incorrect number of parameters.

=head1 MOO VERSUS ANY::MOOSE

L<Any::Moose> will load L<Mouse> normally, and L<Moose> in a program using
L<Moose> - which theoretically allows you to get the startup time of L<Mouse>
without disadvantaging L<Moose> users.

Sadly, this doesn't entirely work, since the selection is load order dependent
- L<Moo>'s metaclass inflation system explained above in L</MOO AND MOOSE> is
significantly more reliable.

So if you want to write a CPAN module that loads fast or has only pure perl
dependencies but is also fully usable by L<Moose> users, you should be using
L<Moo>.

For a full explanation, see the article
L<http://shadow.cat/blog/matt-s-trout/moo-versus-any-moose> which explains
the differing strategies in more detail and provides a direct example of
where L<Moo> succeeds and L<Any::Moose> fails.

=head1 IMPORTED METHODS

=head2 new

 Foo::Bar->new( attr1 => 3 );

or

 Foo::Bar->new({ attr1 => 3 });

=head2 BUILDARGS

 sub BUILDARGS {
   my ( $class, @args ) = @_;

   unshift @args, "attr1" if @args % 2 == 1;

   return { @args };
 };

 Foo::Bar->new( 3 );

The default implementation of this method accepts a hash or hash reference of
named parameters. If it receives a single argument that isn't a hash reference
it throws an error.

You can override this method in your class to handle other types of options
passed to the constructor.

This method should always return a hash reference of named options.

=head2 FOREIGNBUILDARGS

If you are inheriting from a non-Moo class, the arguments passed to the parent
class constructor can be manipulated by defining a C<FOREIGNBUILDARGS> method.
It will receive the same arguments as C<BUILDARGS>, and should return a list
of arguments to pass to the parent class constructor.

=head2 BUILD

Define a C<BUILD> method on your class and the constructor will automatically
call the C<BUILD> method from parent down to child after the object has
been instantiated.  Typically this is used for object validation or possibly
logging.

=head2 DEMOLISH

If you have a C<DEMOLISH> method anywhere in your inheritance hierarchy,
a C<DESTROY> method is created on first object construction which will call
C<< $instance->DEMOLISH($in_global_destruction) >> for each C<DEMOLISH>
method from child upwards to parents.

Note that the C<DESTROY> method is created on first construction of an object
of your class in order to not add overhead to classes without C<DEMOLISH>
methods; this may prove slightly surprising if you try and define your own.

=head2 does

 if ($foo->does('Some::Role1')) {
   ...
 }

Returns true if the object composes in the passed role.

=head1 IMPORTED SUBROUTINES

=head2 extends

 extends 'Parent::Class';

Declares base class. Multiple superclasses can be passed for multiple
inheritance (but please use roles instead).

Calling extends more than once will REPLACE your superclasses, not add to
them like 'use base' would.

=head2 with

 with 'Some::Role1';

or

 with 'Some::Role1', 'Some::Role2';

Composes one or more L<Moo::Role> (or L<Role::Tiny>) roles into the current
class.  An error will be raised if these roles have conflicting methods.

=head2 has

 has attr => (
   is => 'ro',
 );

Declares an attribute for the class.

 package Foo;
 use Moo;
 has 'attr' => (
   is => 'ro'
 );

 package Bar;
 use Moo;
 extends 'Foo';
 has '+attr' => (
   default => sub { "blah" },
 );

Using the C<+> notation, it's possible to override an attribute.

The options for C<has> are as follows:

=over 2

=item * is

B<required>, may be C<ro>, C<lazy>, C<rwp> or C<rw>.

C<ro> generates an accessor that dies if you attempt to write to it - i.e.
a getter only - by defaulting C<reader> to the name of the attribute.

C<lazy> generates a reader like C<ro>, but also sets C<lazy> to 1 and
C<builder> to C<_build_${attribute_name}> to allow on-demand generated
attributes.  This feature was my attempt to fix my incompetence when
originally designing C<lazy_build>, and is also implemented by
L<MooseX::AttributeShortcuts>. There is, however, nothing to stop you
using C<lazy> and C<builder> yourself with C<rwp> or C<rw> - it's just that
this isn't generally a good idea so we don't provide a shortcut for it.

C<rwp> generates a reader like C<ro>, but also sets C<writer> to
C<_set_${attribute_name}> for attributes that are designed to be written
from inside of the class, but read-only from outside.
This feature comes from L<MooseX::AttributeShortcuts>.

C<rw> generates a normal getter/setter by defaulting C<accessor> to the
name of the attribute.

=item * isa

Takes a coderef which is meant to validate the attribute.  Unlike L<Moose>, Moo
does not include a basic type system, so instead of doing C<< isa => 'Num' >>,
one should do

 isa => sub {
   die "$_[0] is not a number!" unless looks_like_number $_[0]
 },

Note that the return value is ignored, only whether the sub lives or
dies matters.

L<Sub::Quote aware|/SUB QUOTE AWARE>

Since L<Moo> does B<not> run the C<isa> check before C<coerce> if a coercion
subroutine has been supplied, C<isa> checks are not structural to your code
and can, if desired, be omitted on non-debug builds (although if this results
in an uncaught bug causing your program to break, the L<Moo> authors guarantee
nothing except that you get to keep both halves).

If you want L<MooseX::Types> style named types, look at
L<MooX::Types::MooseLike>.

To cause your C<isa> entries to be automatically mapped to named
L<Moose::Meta::TypeConstraint> objects (rather than the default behaviour
of creating an anonymous type), set:

  $Moo::HandleMoose::TYPE_MAP{$isa_coderef} = sub {
    require MooseX::Types::Something;
    return MooseX::Types::Something::TypeName();
  };

Note that this example is purely illustrative; anything that returns a
L<Moose::Meta::TypeConstraint> object or something similar enough to it to
make L<Moose> happy is fine.

=item * coerce

Takes a coderef which is meant to coerce the attribute.  The basic idea is to
do something like the following:

 coerce => sub {
   $_[0] % 2 ? $_[0] : $_[0] + 1
 },

Note that L<Moo> will always fire your coercion: this is to permit
C<isa> entries to be used purely for bug trapping, whereas coercions are
always structural to your code. We do, however, apply any supplied C<isa>
check after the coercion has run to ensure that it returned a valid value.

L<Sub::Quote aware|/SUB QUOTE AWARE>

=item * handles

Takes a string

  handles => 'RobotRole'

Where C<RobotRole> is a role (L<Moo::Role>) that defines an interface which
becomes the list of methods to handle.

Takes a list of methods

 handles => [ qw( one two ) ]

Takes a hashref

 handles => {
   un => 'one',
 }

=item * C<trigger>

Takes a coderef which will get called any time the attribute is set. This
includes the constructor, but not default or built values. Coderef will be
invoked against the object with the new value as an argument.

If you set this to just C<1>, it generates a trigger which calls the
C<_trigger_${attr_name}> method on C<$self>. This feature comes from
L<MooseX::AttributeShortcuts>.

Note that Moose also passes the old value, if any; this feature is not yet
supported.

L<Sub::Quote aware|/SUB QUOTE AWARE>

=item * C<default>

Takes a coderef which will get called with $self as its only argument
to populate an attribute if no value is supplied to the constructor - or
if the attribute is lazy, when the attribute is first retrieved if no
value has yet been provided.

If a simple scalar is provided, it will be inlined as a string. Any non-code
reference (hash, array) will result in an error - for that case instead use
a code reference that returns the desired value.

Note that if your default is fired during new() there is no guarantee that
other attributes have been populated yet so you should not rely on their
existence.

L<Sub::Quote aware|/SUB QUOTE AWARE>

=item * C<predicate>

Takes a method name which will return true if an attribute has a value.

If you set this to just C<1>, the predicate is automatically named
C<has_${attr_name}> if your attribute's name does not start with an
underscore, or C<_has_${attr_name_without_the_underscore}> if it does.
This feature comes from L<MooseX::AttributeShortcuts>.

=item * C<builder>

Takes a method name which will be called to create the attribute - functions
exactly like default except that instead of calling

  $default->($self);

Moo will call

  $self->$builder;

The following features come from L<MooseX::AttributeShortcuts>:

If you set this to just C<1>, the builder is automatically named
C<_build_${attr_name}>.

If you set this to a coderef or code-convertible object, that variable will be
installed under C<$class::_build_${attr_name}> and the builder set to the same
name.

=item * C<clearer>

Takes a method name which will clear the attribute.

If you set this to just C<1>, the clearer is automatically named
C<clear_${attr_name}> if your attribute's name does not start with an
underscore, or <_clear_${attr_name_without_the_underscore}> if it does.
This feature comes from L<MooseX::AttributeShortcuts>.

=item * C<lazy>

B<Boolean>.  Set this if you want values for the attribute to be grabbed
lazily.  This is usually a good idea if you have a L</builder> which requires
another attribute to be set.

=item * C<required>

B<Boolean>.  Set this if the attribute must be passed on instantiation.

=item * C<reader>

The value of this attribute will be the name of the method to get the value of
the attribute.  If you like Java style methods, you might set this to
C<get_foo>

=item * C<writer>

The value of this attribute will be the name of the method to set the value of
the attribute.  If you like Java style methods, you might set this to
C<set_foo>.

=item * C<weak_ref>

B<Boolean>.  Set this if you want the reference that the attribute contains to
be weakened; use this when circular references are possible, which will cause
leaks.

=item * C<init_arg>

Takes the name of the key to look for at instantiation time of the object.  A
common use of this is to make an underscored attribute have a non-underscored
initialization name. C<undef> means that passing the value in on instantiation
is ignored.

=item * C<moosify>

Takes either a coderef or array of coderefs which is meant to transform the
given attributes specifications if necessary when upgrading to a Moose role or
class. You shouldn't need this by default, but is provided as a means of
possible extensibility.

=back

=head2 before

 before foo => sub { ... };

See L<< Class::Method::Modifiers/before method(s) => sub { ... } >> for full
documentation.

=head2 around

 around foo => sub { ... };

See L<< Class::Method::Modifiers/around method(s) => sub { ... } >> for full
documentation.

=head2 after

 after foo => sub { ... };

See L<< Class::Method::Modifiers/after method(s) => sub { ... } >> for full
documentation.

=head1 SUB QUOTE AWARE

L<Sub::Quote/quote_sub> allows us to create coderefs that are "inlineable,"
giving us a handy, XS-free speed boost.  Any option that is L<Sub::Quote>
aware can take advantage of this.

To do this, you can write

  use Moo;
  use Sub::Quote;

  has foo => (
    is => 'ro',
    isa => quote_sub(q{ die "Not <3" unless $_[0] < 3 })
  );

which will be inlined as

  do {
    local @_ = ($_[0]->{foo});
    die "Not <3" unless $_[0] < 3;
  }

or to avoid localizing @_,

  has foo => (
    is => 'ro',
    isa => quote_sub(q{ my ($val) = @_; die "Not <3" unless $val < 3 })
  );

which will be inlined as

  do {
    my ($val) = ($_[0]->{foo});
    die "Not <3" unless $val < 3;
  }

See L<Sub::Quote> for more information, including how to pass lexical
captures that will also be compiled into the subroutine.

=head1 INCOMPATIBILITIES WITH MOOSE

There is no built-in type system.  C<isa> is verified with a coderef; if you
need complex types, just make a library of coderefs, or better yet, functions
that return quoted subs. L<MooX::Types::MooseLike> provides a similar API
to L<MooseX::Types::Moose> so that you can write

  has days_to_live => (is => 'ro', isa => Int);

and have it work with both; it is hoped that providing only subrefs as an
API will encourage the use of other type systems as well, since it's
probably the weakest part of Moose design-wise.

C<initializer> is not supported in core since the author considers it to be a
bad idea and Moose best practices recommend avoiding it. Meanwhile C<trigger> or
C<coerce> are more likely to be able to fulfill your needs.

There is no meta object.  If you need this level of complexity you wanted
L<Moose> - Moo succeeds at being small because it explicitly does not
provide a metaprotocol. However, if you load L<Moose>, then

  Class::MOP::class_of($moo_class_or_role)

will return an appropriate metaclass pre-populated by L<Moo>.

No support for C<super>, C<override>, C<inner>, or C<augment> - the author
considers augment to be a bad idea, and override can be translated:

  override foo => sub {
    ...
    super();
    ...
  };

  around foo => sub {
    my ($orig, $self) = (shift, shift);
    ...
    $self->$orig(@_);
    ...
  };

The C<dump> method is not provided by default. The author suggests loading
L<Devel::Dwarn> into C<main::> (via C<perl -MDevel::Dwarn ...> for example) and
using C<$obj-E<gt>$::Dwarn()> instead.

L</default> only supports coderefs and plain scalars, because passing a hash
or array reference as a default is almost always incorrect since the value is
then shared between all objects using that default.

C<lazy_build> is not supported; you are instead encouraged to use the
C<< is => 'lazy' >> option supported by L<Moo> and L<MooseX::AttributeShortcuts>.

C<auto_deref> is not supported since the author considers it a bad idea and
it has been considered best practice to avoid it for some time.

C<documentation> will show up in a L<Moose> metaclass created from your class
but is otherwise ignored. Then again, L<Moose> ignores it as well, so this
is arguably not an incompatibility.

Since C<coerce> does not require C<isa> to be defined but L<Moose> does
require it, the metaclass inflation for coerce alone is a trifle insane
and if you attempt to subtype the result will almost certainly break.

Handling of warnings: when you C<use Moo> we enable FATAL warnings.  The nearest
similar invocation for L<Moose> would be:

  use Moose;
  use warnings FATAL => "all";

Additionally, L<Moo> supports a set of attribute option shortcuts intended to
reduce common boilerplate.  The set of shortcuts is the same as in the L<Moose>
module L<MooseX::AttributeShortcuts> as of its version 0.009+.  So if you:

    package MyClass;
    use Moo;

The nearest L<Moose> invocation would be:

    package MyClass;

    use Moose;
    use warnings FATAL => "all";
    use MooseX::AttributeShortcuts;

or, if you're inheriting from a non-Moose class,

    package MyClass;

    use Moose;
    use MooseX::NonMoose;
    use warnings FATAL => "all";
    use MooseX::AttributeShortcuts;

Finally, Moose requires you to call

    __PACKAGE__->meta->make_immutable;

at the end of your class to get an inlined (i.e. not horribly slow)
constructor. Moo does it automatically the first time ->new is called
on your class. (C<make_immutable> is a no-op in Moo to ease migration.)

An extension L<MooX::late> exists to ease translating Moose packages
to Moo by providing a more Moose-like interface.

=head1 SUPPORT

Users' IRC: #moose on irc.perl.org

=for html <a href="http://chat.mibbit.com/#moose@irc.perl.org">(click for instant chatroom login)</a>

Development and contribution IRC: #web-simple on irc.perl.org

=for html <a href="http://chat.mibbit.com/#web-simple@irc.perl.org">(click for instant chatroom login)</a>

Bugtracker: L<http://rt.cpan.org/NoAuth/Bugs.html?Dist=Moo>

Git repository: L<git://git.shadowcat.co.uk/gitmo/Moo.git>

Git web access: L<http://git.shadowcat.co.uk/gitweb/gitweb.cgi?p=gitmo/Moo.git>

=head1 AUTHOR

mst - Matt S. Trout (cpan:MSTROUT) <mst@shadowcat.co.uk>

=head1 CONTRIBUTORS

dg - David Leadbeater (cpan:DGL) <dgl@dgl.cx>

frew - Arthur Axel "fREW" Schmidt (cpan:FREW) <frioux@gmail.com>

hobbs - Andrew Rodland (cpan:ARODLAND) <arodland@cpan.org>

jnap - John Napiorkowski (cpan:JJNAPIORK) <jjn1056@yahoo.com>

ribasushi - Peter Rabbitson (cpan:RIBASUSHI) <ribasushi@cpan.org>

chip - Chip Salzenberg (cpan:CHIPS) <chip@pobox.com>

ajgb - Alex J. G. Burzyński (cpan:AJGB) <ajgb@cpan.org>

doy - Jesse Luehrs (cpan:DOY) <doy at tozt dot net>

perigrin - Chris Prather (cpan:PERIGRIN) <chris@prather.org>

Mithaldu - Christian Walde (cpan:MITHALDU) <walde.christian@googlemail.com>

ilmari - Dagfinn Ilmari Mannsåker (cpan:ILMARI) <ilmari@ilmari.org>

tobyink - Toby Inkster (cpan:TOBYINK) <tobyink@cpan.org>

haarg - Graham Knop (cpan:HAARG) <haarg@cpan.org>

mattp - Matt Phillips (cpan:MATTP) <mattp@cpan.org>

=head1 COPYRIGHT

Copyright (c) 2010-2011 the Moo L</AUTHOR> and L</CONTRIBUTORS>
as listed above.

=head1 LICENSE

This library is free software and may be distributed under the same terms
as perl itself. See L<http://dev.perl.org/licenses/>.

=cut
