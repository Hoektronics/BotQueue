# Copyright 2010, 2011, 2012, 2013 Kevin Ryde

# This file is part of Math-PlanePath.
#
# Math-PlanePath is free software; you can redistribute it and/or modify it
# under the terms of the GNU General Public License as published by the Free
# Software Foundation; either version 3, or (at your option) any later
# version.
#
# Math-PlanePath is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
# or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
# for more details.
#
# You should have received a copy of the GNU General Public License along
# with Math-PlanePath.  If not, see <http://www.gnu.org/licenses/>.


package Math::PlanePath::Base::Generic;
use 5.004;
use strict;

use vars '$VERSION','@ISA','@EXPORT_OK';
$VERSION = 111;

use Exporter;
@ISA = ('Exporter');
@EXPORT_OK = ('round_nearest',

              # not documented yet
              'is_infinite',
              'floor',
              'xy_is_even');

# uncomment this to run the ### lines
#use Smart::Comments;


# with a view to being friendly to BigRat/BigFloat
sub round_nearest {
  my ($x) = @_;
  ### round_nearest(): "$x", $x

  # BigRat through to perl 5.12.4 has some dodginess giving a bigint -0
  # which is considered !=0.  Adding +0 to numify seems to avoid the problem.
  my $int = int($x) + 0;
  if ($x == $int) {
    ### is an integer ...
    return $x;
  }
  $x -= $int;
  ### int:  "$int"
  ### frac: "$x"
  if ($x >= .5) {
    ### round up ...
    return $int + 1;
  }
  if ($x < -.5) {
    ### round down ...
    return $int - 1;
  }
  ### within +/- .5 ...
  return $int;
}

use constant parameter_info_nstart1 => { name        => 'n_start',
                                         share_key   => 'n_start_1',
                                         type        => 'integer',
                                         default     => 1,
                                         width       => 3,
                                         description => 'Starting N.',
                                       };

#------------------------------------------------------------------------------
# these not documented ...

sub is_infinite {
  my ($x) = @_;
  return ($x != $x         # nan
          || ($x != 0 && $x == 2*$x));  # inf
}

# With a view to being friendly to BigRat/BigFloat.
#
# For reference, POSIX::floor() in perl 5.12.4 is a bit bizarre on UV=64bit
# and NV=53bit double.  UV=2^64-1 rounds up to NV=2^64 which floor() then
# returns, so floor() in fact increases the value of what was an integer
# already.
#
# not documented yet
sub floor {
  my ($x) = @_;
  ### floor(): "$x", $x
  my $int = int($x);
  if ($x == $int) {
    ### is an integer ...
    return $x;
  }
  $x -= $int;
  ### frac: "$x"
  if ($x >= 0) {
    ### frac is non-negative ...
    return $int;
  } else {
    ### frac is negative ...
    return $int-1;
  }
}

# not documented yet
sub xy_is_visited_quad1 {
  my ($self, $x, $y) = @_;
  return ! (2*$x < -1 || 2*$y < -1);
}
# not documented yet
sub xy_is_visited_quad12 {
  my ($self, $x, $y) = @_;
  return (2*$y >= -1);
}
# not documented yet
sub _xy_is_visited_x_positive {
  my ($self, $x, $y) = @_;
  return (2*$x >= -1);
}
# not documented yet
sub xy_is_even {
  my ($self, $x, $y) = @_;
  return (round_nearest($x)%2 == round_nearest($y)%2);
}

1;
__END__

=for stopwords Ryde Math-PlanePath PlanePath hashref initializer

=head1 NAME

Math::PlanePath::Base::Generic -- various path helpers

=for test_synopsis my $x

=head1 SYNOPSIS

 use Math::PlanePath::Base::Generic 'round_nearest';
 $x = round_nearest($x);

=head1 DESCRIPTION

This is a few generic helper functions for the PlanePath code.  They're
designed to work on plain Perl integers and floats and in some cases there's
some special support for C<Math::BigInt>.

=head1 EXPORTS

Nothing is exported by default but each function below can be as in the
usual L<Exporter> style,

    use Math::PlanePath::Base::Generic 'round_nearest';

(But not C<parameter_info_nstart1()>, for the reason described below.)

=head1 FUNCTIONS

=head2 Generic

=over 4

=item C<$x = round_nearest ($x)>

Return C<$x> rounded to the nearest integer.  If C<$x> is half way, such as
2.5 then it's round upwards to 3.

   $x = round_nearest($x);

=item C<$href = Math::PlanePath::Base::Generic::parameter_info_nstart1()>

Return an C<n_start> parameter hashref suitable for use in a
C<parameter_info_array()>.  For example,

    # alone
    package Math::PlanePath::MySubclass;
    use constant parameter_info_array =>
      [ Math::PlanePath::Base::Generic::parameter_info_nstart1() ];

    # or with other parameters too
    package Math::PlanePath::MySubclass;
    use constant parameter_info_array =>
      [
       { name            => 'something',
         type            => 'integer',
         default         => '123',
       },
       Math::PlanePath::Base::Generic::parameter_info_nstart1(),
      ];

This function is not exportable since it's meant for a one-off call in an
initializer and so no need to import it for repeated use.

=back

=head1 SEE ALSO

L<Math::PlanePath>,
L<Math::PlanePath::Base::Digits>

=head1 HOME PAGE

http://user42.tuxfamily.org/math-planepath/index.html

=head1 LICENSE

Copyright 2010, 2011, 2012, 2013 Kevin Ryde

This file is part of Math-PlanePath.

Math-PlanePath is free software; you can redistribute it and/or modify it
under the terms of the GNU General Public License as published by the Free
Software Foundation; either version 3, or (at your option) any later
version.

Math-PlanePath is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
more details.

You should have received a copy of the GNU General Public License along with
Math-PlanePath.  If not, see <http://www.gnu.org/licenses/>.

=cut
