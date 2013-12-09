package strictures;

use strict;
use warnings FATAL => 'all';

BEGIN {
  *_PERL_LT_5_8_4 = ($] < 5.008004) ? sub(){1} : sub(){0};
}

our $VERSION = '1.005001'; # 1.5.1

sub VERSION {
  for ($_[1]) {
    last unless defined && !ref && int != 1;
    die "Major version specified as $_ - this is strictures version 1";
  }
  # disable this since Foo->VERSION(undef) correctly returns the version
  # and that can happen either if our caller passes undef explicitly or
  # because the for above autovivified $_[1] - I could make it stop but
  # it's pointless since we don't want to blow up if the caller does
  # something valid either.
  no warnings 'uninitialized';
  shift->SUPER::VERSION(@_);
}

our $extra_load_states;

our $Smells_Like_VCS = (-e '.git' || -e '.svn' || -e '.hg'
  || (-e '../../dist.ini'
      && (-e '../../.git' || -e '../../.svn' || -e '../../.hg' )));

sub import {
  strict->import;
  warnings->import(FATAL => 'all');

  my $extra_tests = do {
    if (exists $ENV{PERL_STRICTURES_EXTRA}) {
      if (_PERL_LT_5_8_4 and $ENV{PERL_STRICTURES_EXTRA}) {
        die 'PERL_STRICTURES_EXTRA checks are not available on perls older than 5.8.4: '
          . "please unset \$ENV{PERL_STRICTURES_EXTRA}\n";
      }
      $ENV{PERL_STRICTURES_EXTRA};
    } elsif (! _PERL_LT_5_8_4) {
      !!((caller)[1] =~ /^(?:t|xt|lib|blib)/
         and $Smells_Like_VCS)
    }
  };
  if ($extra_tests) {
    $extra_load_states ||= do {

      my (%rv, @failed);
      foreach my $mod (qw(indirect multidimensional bareword::filehandles)) {
        eval "require $mod; \$rv{'$mod'} = 1;" or do {
          push @failed, $mod;

          # courtesy of the 5.8 require bug
          # (we do a copy because 5.16.2 at least uses the same read-only
          # scalars for the qw() list and it doesn't seem worth a $^V check)

          (my $file = $mod) =~ s|::|/|g;
          delete $INC{"${file}.pm"};
        };
      }

      if (@failed) {
        my $failed = join ' ', @failed;
        print STDERR <<EOE;
strictures.pm extra testing active but couldn't load all modules. Missing were:

  $failed

Extra testing is auto-enabled in checkouts only, so if you're the author
of a strictures-using module you need to run:

  cpan indirect multidimensional bareword::filehandles

but these modules are not required by your users.
EOE
      }

      \%rv;
    };

    indirect->unimport(':fatal') if $extra_load_states->{indirect};
    multidimensional->unimport if $extra_load_states->{multidimensional};
    bareword::filehandles->unimport if $extra_load_states->{'bareword::filehandles'};
  }
}

1;

__END__
=head1 NAME

strictures - turn on strict and make all warnings fatal

=head1 SYNOPSIS

  use strictures 1;

is equivalent to

  use strict;
  use warnings FATAL => 'all';

except when called from a file which matches:

  (caller)[1] =~ /^(?:t|xt|lib|blib)/

and when either C<.git>, C<.svn>, or C<.hg> is present in the current directory (with
the intention of only forcing extra tests on the author side) -- or when C<.git>,
C<.svn>, or C<.hg> is present two directories up along with C<dist.ini> (which would
indicate we are in a C<dzil test> operation, via L<Dist::Zilla>) --
or when the C<PERL_STRICTURES_EXTRA> environment variable is set, in which case

  use strictures 1;

is equivalent to

  use strict;
  use warnings FATAL => 'all';
  no indirect 'fatal';
  no multidimensional;
  no bareword::filehandles;

Note that C<PERL_STRICTURES_EXTRA> may at some point add even more tests, with only a minor
version increase, but any changes to the effect of C<use strictures> in
normal mode will involve a major version bump.

If any of the extra testing modules are not present, L<strictures> will
complain loudly, once, via C<warn()>, and then shut up. But you really
should consider installing them, they're all great anti-footgun tools.

=head1 DESCRIPTION

I've been writing the equivalent of this module at the top of my code for
about a year now. I figured it was time to make it shorter.

Things like the importer in C<use Moose> don't help me because they turn
warnings on but don't make them fatal -- which from my point of view is
useless because I want an exception to tell me my code isn't warnings-clean.

Any time I see a warning from my code, that indicates a mistake.

Any time my code encounters a mistake, I want a crash -- not spew to STDERR
and then unknown (and probably undesired) subsequent behaviour.

I also want to ensure that obvious coding mistakes, like indirect object
syntax (and not so obvious mistakes that cause things to accidentally compile
as such) get caught, but not at the cost of an XS dependency and not at the
cost of blowing things up on another machine.

Therefore, L<strictures> turns on additional checking, but only when it thinks
it's running in a test file in a VCS checkout -- although if this causes
undesired behaviour this can be overridden by setting the
C<PERL_STRICTURES_EXTRA> environment variable.

If additional useful author side checks come to mind, I'll add them to the
C<PERL_STRICTURES_EXTRA> code path only -- this will result in a minor version increase (e.g.
1.000000 to 1.001000 (1.1.0) or similar). Any fixes only to the mechanism of
this code will result in a sub-version increase (e.g. 1.000000 to 1.000001
(1.0.1)).

If the behaviour of C<use strictures> in normal mode changes in any way, that
will constitute a major version increase -- and the code already checks
when its version is tested to ensure that

  use strictures 1;

will continue to only introduce the current set of strictures even if 2.0 is
installed.

=head1 METHODS

=head2 import

This method does the setup work described above in L</DESCRIPTION>

=head2 VERSION

This method traps the C<< strictures->VERSION(1) >> call produced by a use line
with a version number on it and does the version check.

=head1 EXTRA TESTING RATIONALE

Every so often, somebody complains that they're deploying via C<git pull>
and that they don't want L<strictures> to enable itself in this case -- and that
setting C<PERL_STRICTURES_EXTRA> to 0 isn't acceptable (additional ways to
disable extra testing would be welcome but the discussion never seems to get
that far).

In order to allow us to skip a couple of stages and get straight to a
productive conversation, here's my current rationale for turning the
extra testing on via a heuristic:

The extra testing is all stuff that only ever blows up at compile time;
this is intentional. So the oft-raised concern that it's different code being
tested is only sort of the case -- none of the modules involved affect the
final optree to my knowledge, so the author gets some additional compile
time crashes which he/she then fixes, and the rest of the testing is
completely valid for all environments.

The point of the extra testing -- especially C<no indirect> -- is to catch
mistakes that newbie users won't even realise are mistakes without
help. For example,

  foo { ... };

where foo is an & prototyped sub that you forgot to import -- this is
pernicious to track down since all I<seems> fine until it gets called
and you get a crash. Worse still, you can fail to have imported it due
to a circular require, at which point you have a load order dependent
bug which I've seen before now I<only> show up in production due to tiny
differences between the production and the development environment. I wrote
L<http://shadow.cat/blog/matt-s-trout/indirect-but-still-fatal/> to explain
this particular problem before L<strictures> itself existed.

As such, in my experience so far L<strictures>' extra testing has
I<avoided> production versus development differences, not caused them.

Additionally, L<strictures>' policy is very much "try and provide as much
protection as possible for newbies -- who won't think about whether there's
an option to turn on or not" -- so having only the environment variable
is not sufficient to achieve that (I get to explain that you need to add
C<use strict> at least once a week on freenode #perl -- newbies sometimes
completely skip steps because they don't understand that that step
is important).

I make no claims that the heuristic is perfect -- it's already been evolved
significantly over time, especially for 1.004 where we changed things to
ensure it only fires on files in your checkout (rather than L<strictures>-using
modules you happened to have installed, which was just silly). However, I
hope the above clarifies why a heuristic approach is not only necessary but
desirable from a point of view of providing new users with as much safety as possible,
and will allow any future discussion on the subject to focus on "how do we
minimise annoyance to people deploying from checkouts intentionally".

=head1 SEE ALSO

=over 4

=item *

L<indirect>

=item *

L<multidimensional>

=item *

L<bareword::filehandles>

=back

=head1 COMMUNITY AND SUPPORT

=head2 IRC channel

irc.perl.org #toolchain

(or bug 'mst' in query on there or freenode)

=head2 Git repository

Gitweb is on http://git.shadowcat.co.uk/ and the clone URL is:

  git clone git://git.shadowcat.co.uk/p5sagit/strictures.git

The web interface to the repository is at:

  http://git.shadowcat.co.uk/gitweb/gitweb.cgi?p=p5sagit/strictures.git

=head1 AUTHOR

mst - Matt S. Trout (cpan:MSTROUT) <mst@shadowcat.co.uk>

=head1 CONTRIBUTORS

Karen Etheridge (cpan:ETHER) <ether@cpan.org>

Mithaldu - Christian Walde (cpan:MITHALDU) <walde.christian@gmail.com>

haarg - Graham Knop (cpan:HAARG) <haarg@haarg.org>

=head1 COPYRIGHT

Copyright (c) 2010 the strictures L</AUTHOR> and L</CONTRIBUTORS>
as listed above.

=head1 LICENSE

This library is free software and may be distributed under the same terms
as perl itself.

=cut
