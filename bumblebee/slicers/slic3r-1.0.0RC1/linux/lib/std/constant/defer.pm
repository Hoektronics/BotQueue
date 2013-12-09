# Copyright 2008, 2009, 2010, 2011 Kevin Ryde

# This file is part of constant-defer.
#
# constant-defer is free software; you can redistribute it and/or modify it
# under the terms of the GNU General Public License as published by the Free
# Software Foundation; either version 3, or (at your option) any later
# version.
#
# constant-defer is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
# or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
# for more details.
#
# You should have received a copy of the GNU General Public License along
# with constant-defer.  If not, see <http://www.gnu.org/licenses/>.

package constant::defer;
use strict;
use vars qw($VERSION);

$VERSION = 5;

sub import {
  my $class = shift;
  $class->_create_for_package (scalar(caller), @_);
}
sub _create_for_package {
  my $class = shift;
  my $target_package = shift;
  while (@_) {
    my $name = shift;
    if (ref $name eq 'HASH') {
      unshift @_, %$name;
      next;
    }
    unless (@_) {
      require Carp;
      Carp::croak ("Missing value sub for $name");
    }
    my $subr = shift;

    ### $constant::defer::DEBUG_LAST_SUBR = $subr;

    my ($fullname, $basename);
    if ($name =~ /::([^:]*)$/s) {
      $fullname = $name;
      $basename = $1;
    } else {
      $basename = $name;
      $fullname = "${target_package}::$name";
    }
    ## print "constant::defer $arg -- $fullname $basename $old\n";
    $class->_validate_name ($basename);
    $class->_create_fullname ($fullname, $subr);
  }
}

sub _create_fullname {
  my ($class, $fullname, $subr) = @_;
  my $run = sub {
    unshift @_, $fullname, $subr;
    goto &_run;
  };
  my $func = sub () {
    unshift @_, \$run;
    goto $run;
  };
  no strict 'refs';
  *$fullname = $func;

  ### $constant::defer::DEBUG_LAST_RUNNER = $run;
}

sub _run {
  my $fullname = shift;
  my $subr = shift;
  my $run_ref = shift;
  ### print "_run() $fullname $subr\n";

  my @ret = &$subr(@_);
  if (@ret == 1) {
    # constant.pm has an optimization to make a constant by storing a scalar
    # value directly into the %{Foo::Bar::} hash if there's no typeglob for
    # the name yet.  But that doesn't apply here, there's always a glob from
    # having converted a function.
    #
    # The function created only has name __ANON__ in its coderef GV (as
    # fetched by Sub::Identify for instance).  This is the same as most
    # function creating modules, including Memoize.pm.  Plain constant.pm
    # likewise, except when it uses the scalar ref in symbol table
    # optimization, in that case a later upgrade to a function gets a name.
    #
    my $value = $ret[0];
    $subr = sub () { $value };

  } elsif (@ret == 0) {
    $subr = \&_nothing;

  } else {
    $subr = sub () { @ret };
  }

  $$run_ref = $subr;
  { no strict 'refs';
    local $^W = 0; # no warnings 'redefine';
    eval { *$fullname = $subr } or die $@;
  }
  goto $subr;
}

# not as strict as constant.pm
sub _validate_name {
  my ($class, $name) = @_;
  if ($name =~ m{[()]   # no parens like CODE(0x1234) if miscounted args
               |^[0-9]  # no starting with a number
               |^$      # not empty
              }x) {
    require Carp;
    Carp::croak ("Constant name '$name' is invalid");
  }
}

sub _nothing () { } ## no critic (ProhibitSubroutinePrototypes)

1;
__END__

=for stopwords bareword stringizing inline there'd fakery subclassing Ryde multi-value inlined coderef subrs subr

=head1 NAME

constant::defer -- constant subs with deferred value calculation

=for test_synopsis my ($some,$thing,$an,$other);

=head1 SYNOPSIS

 use constant::defer FOO => sub { return $some + $thing; },
                     BAR => sub { return $an * $other; };

 use constant::defer MYOBJ => sub { require My::Class;
                                    return My::Class->new_thing; }

=head1 DESCRIPTION

C<constant::defer> creates a subroutine which on the first call runs given
code to calculate its value, and on the second and subsequent calls just
returns that value, like a constant.  The value code is discarded once run,
allowing it to be garbage collected.

Deferring a calculation is good if it might take a lot of work or produce a
big result, but is only needed sometimes or only well into a program run.
If it's never needed then the value code never runs.

A deferred constant is generally not inlined or folded (see
L<perlop/Constant Folding>) like a plain C<constant> since it's not a single
scalar value.  In the current implementation a deferred constant becomes a
plain one after the first use, so may inline etc in code compiled after that
(see L</IMPLEMENTATION> below).

=head2 Uses

Here are some typical uses.

=over 4

=item *

A big value or slow calculation only sometimes needed,

    use constant::defer SLOWVALUE => sub {
                          long calculation ...;
                          return $result;
                        };

    if ($option) {
      print "s=", SLOWVALUE, "\n";
    }

=item *

A shared object instance created when needed then re-used,

    use constant::defer FORMATTER =>
          sub { return My::Formatter->new };

    if ($something) {
      FORMATTER()->format ...
    }

=item *

The value code might load requisite modules too, again deferring that until
actually needed,

    use constant::defer big => sub {
      require Some::Big::Module;
      return Some::Big::Module->create_something(...);
    };

=item *

Once-only setup code can be created with no return value.  The code is
garbage collected after the first run and becomes a do-nothing.  Remember to
have an empty return statement so as not to keep the last expression's value
alive forever.

    use constant::defer MY_INIT => sub {
      many lines of setup code ...;
      return;
    };

    sub new {
      MY_INIT();
      ...
    }

=back

=head1 IMPORTS

There are no functions as such, everything works through the C<use> import.

=over 4

=item C<< use constant::defer NAME1=>SUB1, NAME2=>SUB2, ...; >>

The parameters are name/subroutine pairs.  For each one a sub called C<NAME>
is created, running the given C<SUB> the first time its value is needed.

C<NAME> defaults to the caller's package, or a fully qualified name can be
given.  Remember that the bareword stringizing of C<=E<gt>> doesn't act on a
qualified name, so add quotes in that case.

    use constant::defer 'Other::Package::BAR' => sub { ... };

For compatibility with the C<constant> module a hash of name/sub arguments
is accepted too.  But C<constant::defer> doesn't need that since there's
only ever one thing (a sub) following each name.

    use constant::defer { FOO => sub { ... },
                          BAR => sub { ... } };

    # works without the hashref too
    use constant::defer FOO => sub { ... },
                        BAR => sub { ... };

=back

=head1 MULTIPLE VALUES

The value sub can return multiple values to make an array style constant
sub.

    use constant::defer NUMS => sub { return ('one', 'two') };

    foreach (NUMS) {
       print $_,"\n";
    }

The value sub is always run in array context, for consistency, irrespective
how the constant is used.  The return from the new constant sub is an array
style

    sub () { return @result }

If the value sub was a list-style return like C<NUMS> shown above, then this
array-style return is slightly different.  In scalar context a list return
means the last value (like a comma operator), but an array return in scalar
context means the number of elements.  A multi-value constant won't normally
be used in scalar context, so the difference shouldn't arise.  The array
style is easier for C<constant::defer> to implement and is the same as the
plain C<constant> module does.

=head1 ARGUMENTS

If the constant is called with arguments then they're passed on to the value
sub.  This can be good for constants used as object or class methods.
Passing anything to plain constants would be unusual.

One cute use for a class method style is to make a "singleton" instance of
the class.  See F<examples/instance.pl> in the sources for a complete
program.

    package My::Class;
    use constant::defer INSTANCE => sub { my ($class) = @_;
                                          return $class->new };
    package main;
    $obj = My::Class->INSTANCE;

A subclass might want to be careful about letting a subclass object get into
the parent C<INSTANCE>, though if a program only ever used the subclass then
that might in fact be desirable.

Subs created by C<constant::defer> always have prototype C<()>, ensuring
they always parse the same way.  The prototype has no effect when called as
a method like above, but if you want a plain call with arguments then use
C<&> to bypass the prototype (see L<perlsub>).

    &MYCONST ('Some value');

=head1 IMPLEMENTATION

Currently C<constant::defer> creates a sub under the requested name and when
called it replaces that with a new constant sub the same as C<use constant>
would make.  This is compact and means that later loaded code might be able
to inline it.

It's fine to keep a reference to the initial sub and in fact that happens
quite normally if importing into another module (with the usual
C<Exporter>), or an explicit C<\&foo>, or a C<$package-E<gt>can('foo')>.
The initial sub changes itself to jump to the new constant, it doesn't
re-run the value code.

The jump is currently done by a C<goto> to the new coderef, so it's a touch
slower than the new constant sub directly.  A spot of XS would no doubt make
the difference negligible, in fact perhaps to the point where there'd be no
need for a new sub, just have the initial transform itself.  If the new form
looked enough like a plain constant it might inline in later loaded code.

For reference, C<Package::Constants> (as of version 0.02) considers
C<constant::defer> subrs as constants, both before and after the first call
that runs the value code.  C<Package::Constants> just looks for prototyped
S<C<sub foo () { }>> functions, so any such subr rates as a constant.

=head1 OTHER WAYS TO DO IT

There's many ways to do "deferred" or "lazy" calculations.

=over 4

=item *

C<Memoize> makes a function repeat its return.  Results are cached against
the arguments, so it keeps the original code whereas C<constant::defer>
discards after the first run.

=item *

C<Class::Singleton> and friends make a create-once
C<My::Class-E<gt>instance> method.  C<constant::defer> can get close with
the fakery shown under L</ARGUMENTS> above, though without a C<has_instance>
to query.

=item *

C<Sub::Become> offers some syntactic sugar for redefining the running
subroutine, including to a constant.

=item *

C<Sub::SingletonBuilder> can create an instance function for a class.  It's
geared towards objects and so won't allow 0 or C<undef> as the return value.

=item *

A scalar can be rigged up to run code on its first access.  C<Data::Lazy>
uses a C<tie>.  C<Scalar::Defer> and C<Scalar::Lazy> use C<overload> on an
object.  C<Data::Thunk> optimizes out the object from C<Scalar::Defer> after
the first run.  C<Variable::Lazy> uses XS magic removed after the first
fetch and some parsing for syntactic sugar.

The advantage of a variable is that it interpolates in strings, but it won't
inline in later loaded code; sloppy XS code might bypass the magic; and
package variables aren't very friendly when subclassing.

=item *

C<Object::Lazy> and C<Object::Trampoline> rig up an object wrapper to load
and create an actual object only when a method is called, dispatching to it
and replacing the callers C<$_[0]>.  The advantage is you can pass the
wrapper object around, etc, deferring creation to an even later point than a
sub or scalar can.

=item *

C<Object::Realize::Later>, C<Class::LazyObject> and C<Class::LazyFactory>
help make a defer class which transforms lazy stub objects to real ones when
a method call is made.  A separate defer class is required for each real
class.

=item *

C<once.pm> sets up a run-once code block, but with no particular return
value and not discarding the code after run.

=item *

C<Class::LazyLoad> and C<deferred> load code on a class method call such as
object creation.  They're more about module loading than a defer of a
particular value.

=item *

C<Tie::LazyList> and C<Tie::Array::Lazy> makes an array calculate values
on-demand from a generator function.  C<Hash::Lazy> does a similar thing for
hashes.  C<Tie::LazyFunction> hides a function behind a scalar; its laziness
is in the argument evaluation, the function is called every time.

=back

=head1 SEE ALSO

L<constant>, L<perlsub>, L<constant::lexical>

L<Memoize>, L<Attribute::Memoize>, L<Memoize::Attrs>,
L<Class::Singleton>,
L<Data::Lazy>, L<Scalar::Defer>, L<Scalar::Lazy>, L<Data::Thunk>,
L<Variable::Lazy>,
L<Sub::Become>,
L<Sub::SingletonBuilder>,
L<Object::Lazy>,
L<Object::Trampoline>,
L<Object::Realize::Later>,
L<once>,
L<Class::LazyLoad>,
L<deferred>

=head1 HOME PAGE

http://user42.tuxfamily.org/constant-defer/index.html

=head1 COPYRIGHT

Copyright 2009, 2010, 2011 Kevin Ryde

constant-defer is free software; you can redistribute it and/or modify it
under the terms of the GNU General Public License as published by the Free
Software Foundation; either version 3, or (at your option) any later
version.

constant-defer is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
more details.

You should have received a copy of the GNU General Public License along with
constant-defer.  If not, see <http://www.gnu.org/licenses/>.

=cut
