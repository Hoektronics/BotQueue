package Sub::Quote;

use strictures 1;

sub _clean_eval { eval $_[0] }

use Sub::Defer;
use B 'perlstring';
use Scalar::Util qw(weaken);
use base qw(Exporter);

our $VERSION = '1.003001';
$VERSION = eval $VERSION;

our @EXPORT = qw(quote_sub unquote_sub quoted_from_sub);

our %QUOTED;

our %WEAK_REFS;

sub capture_unroll {
  my ($from, $captures, $indent) = @_;
  join(
    '',
    map {
      /^([\@\%\$])/
        or die "capture key should start with \@, \% or \$: $_";
      (' ' x $indent).qq{my ${_} = ${1}{${from}->{${\perlstring $_}}};\n};
    } keys %$captures
  );
}

sub inlinify {
  my ($code, $args, $extra, $local) = @_;
  my $do = 'do { '.($extra||'');
  if (my ($code_args, $body) = $code =~ / +my \(([^)]+)\) = \@_;(.*)$/s) {
    if ($code_args eq $args) {
      $do.$body.' }'
    } else {
      $do.'my ('.$code_args.') = ('.$args.'); '.$body.' }';
    }
  } else {
    my $assign = '';
    if ($local || $args ne '@_') {
      $assign = ($local ? 'local ' : '').'@_ = ('.$args.'); ';
    }
    $do.$assign.$code.' }';
  }
}

sub quote_sub {
  # HOLY DWIMMERY, BATMAN!
  # $name => $code => \%captures => \%options
  # $name => $code => \%captures
  # $name => $code
  # $code => \%captures => \%options
  # $code
  my $options =
    (ref($_[-1]) eq 'HASH' and ref($_[-2]) eq 'HASH')
      ? pop
      : {};
  my $captures = pop if ref($_[-1]) eq 'HASH';
  undef($captures) if $captures && !keys %$captures;
  my $code = pop;
  my $name = $_[0];
  my $quoted_info;
  my $deferred = defer_sub +($options->{no_install} ? undef : $name) => sub {
    unquote_sub($quoted_info->[4]);
  };
  $quoted_info = [ $name, $code, $captures, undef, $deferred ];
  weaken($QUOTED{$deferred} = $quoted_info);
  return $deferred;
}

sub quoted_from_sub {
  my ($sub) = @_;
  $QUOTED{$sub||''};
}

sub unquote_sub {
  my ($sub) = @_;
  unless ($QUOTED{$sub}[3]) {
    my ($name, $code, $captures) = @{$QUOTED{$sub}};

    my $make_sub = "{\n";

    my %captures = $captures ? %$captures : ();
    $captures{'$_QUOTED'} = \$QUOTED{$sub};
    $make_sub .= capture_unroll("\$_[1]", \%captures, 2);

    $make_sub .= (
      $name
          # disable the 'variable $x will not stay shared' warning since
          # we're not letting it escape from this scope anyway so there's
          # nothing trying to share it
        ? "  no warnings 'closure';\n  sub ${name} {\n"
        : "  \$_QUOTED->[3] = sub {\n"
    );
    $make_sub .= $code;
    $make_sub .= "  }".($name ? '' : ';')."\n";
    if ($name) {
      $make_sub .= "  \$_QUOTED->[3] = \\&${name}\n";
    }
    $make_sub .= "}\n1;\n";
    $ENV{SUB_QUOTE_DEBUG} && warn $make_sub;
    {
      local $@;
      no strict 'refs';
      local *{$name} if $name;
      unless (_clean_eval $make_sub, \%captures) {
        die "Eval went very, very wrong:\n\n${make_sub}\n\n$@";
      }
    }
  }
  $QUOTED{$sub}[3];
}

sub CLONE {
  %QUOTED = map { defined $_ ? ($_->[4] => $_) : () } values %QUOTED;
  weaken($_) for values %QUOTED;
}

1;

=head1 NAME

Sub::Quote - efficient generation of subroutines via string eval

=head1 SYNOPSIS

 package Silly;

 use Sub::Quote qw(quote_sub unquote_sub quoted_from_sub);

 quote_sub 'Silly::kitty', q{ print "meow" };

 quote_sub 'Silly::doggy', q{ print "woof" };

 my $sound = 0;

 quote_sub 'Silly::dagron',
   q{ print ++$sound % 2 ? 'burninate' : 'roar' },
   { '$sound' => \$sound };

And elsewhere:

 Silly->kitty;  # meow
 Silly->doggy;  # woof
 Silly->dagron; # burninate
 Silly->dagron; # roar
 Silly->dagron; # burninate

=head1 DESCRIPTION

This package provides performant ways to generate subroutines from strings.

=head1 SUBROUTINES

=head2 quote_sub

 my $coderef = quote_sub 'Foo::bar', q{ print $x++ . "\n" }, { '$x' => \0 };

Arguments: ?$name, $code, ?\%captures, ?\%options

C<$name> is the subroutine where the coderef will be installed.

C<$code> is a string that will be turned into code.

C<\%captures> is a hashref of variables that will be made available to the
code.  The keys should be the full name of the variable to be made available,
including the sigil.  The values should be references to the values.  The
variables will contain copies of the values.  See the L</SYNOPSIS>'s
C<Silly::dagron> for an example using captures.

=head3 options

=over 2

=item * no_install

B<Boolean>.  Set this option to not install the generated coderef into the
passed subroutine name on undefer.

=back

=head2 unquote_sub

 my $coderef = unquote_sub $sub;

Forcibly replace subroutine with actual code.

If $sub is not a quoted sub, this is a no-op.

=head2 quoted_from_sub

 my $data = quoted_from_sub $sub;

 my ($name, $code, $captures, $compiled_sub) = @$data;

Returns original arguments to quote_sub, plus the compiled version if this
sub has already been unquoted.

Note that $sub can be either the original quoted version or the compiled
version for convenience.

=head2 inlinify

 my $prelude = capture_unroll '$captures', {
   '$x' => 1,
   '$y' => 2,
 };

 my $inlined_code = inlinify q{
   my ($x, $y) = @_;

   print $x + $y . "\n";
 }, '$x, $y', $prelude;

Takes a string of code, a string of arguments, a string of code which acts as a
"prelude", and a B<Boolean> representing whether or not to localize the
arguments.

=head2 capture_unroll

 my $prelude = capture_unroll '$captures', {
   '$x' => 1,
   '$y' => 2,
 }, 4;

Arguments: $from, \%captures, $indent

Generates a snippet of code which is suitable to be used as a prelude for
L</inlinify>.  C<$from> is a string will be used as a hashref in the resulting
code.  The keys of C<%captures> are the names of the variables and the values
are ignored.  C<$indent> is the number of spaces to indent the result by.

=head1 CAVEATS

Much of this is just string-based code-generation, and as a result, a few caveats
apply.

=head2 return

Calling C<return> from a quote_sub'ed sub will not likely do what you intend.
Instead of returning from the code you defined in C<quote_sub>, it will return
from the overall function it is composited into.

So when you pass in:

   quote_sub q{  return 1 if $condition; $morecode }

It might turn up in the intended context as follows:

  sub foo {

    <important code a>
    do {
      return 1 if $condition;
      $morecode
    };
    <important code b>

  }

Which will obviously return from foo, when all you meant to do was return from
the code context in quote_sub and proceed with running important code b.

=head2 strictures

Sub::Quote compiles quoted subs in an environment where C<< use strictures >>
is in effect. L<strictures> enables L<strict> and FATAL L<warnings>.

The following dies I<< Use of uninitialized value in print... >>

 no warnings;
 quote_sub 'Silly::kitty', q{ print undef };

If you need to disable parts of strictures, do it within the quoted sub:

 quote_sub 'Silly::kitty', q{ no warnings; print undef };

=head1 SUPPORT

See L<Moo> for support and contact information.

=head1 AUTHORS

See L<Moo> for authors.

=head1 COPYRIGHT AND LICENSE

See L<Moo> for the copyright and license.
