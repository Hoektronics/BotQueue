# Copyright 2010, 2011, 2012, 2013 Kevin Ryde

# This file is part of Math-PlanePath.
#
# Math-PlanePath is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by the
# Free Software Foundation; either version 3, or (at your option) any later
# version.
#
# Math-PlanePath is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
# or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License
# for more details.
#
# You should have received a copy of the GNU General Public License along
# with Math-PlanePath.  If not, see <http://www.gnu.org/licenses/>.


# could loop by more or less, eg. 4*n^2 each time like a square spiral
# (Kevin Vicklund at the_surprises_never_eend_the_u.php)


package Math::PlanePath::SacksSpiral;
use 5.004;
use strict;
use Math::Libm 'hypot';
use POSIX 'floor';
#use List::Util 'max';
*max = \&Math::PlanePath::_max;

use Math::PlanePath;
use Math::PlanePath::MultipleRings;

use vars '$VERSION', '@ISA';
$VERSION = 111;
@ISA = ('Math::PlanePath');


# uncomment this to run the ### lines
#use Smart::Comments;


use constant n_start => 0;
use constant figure => 'circle';

use constant 1.02; # for leading underscore
use constant _TWO_PI => 4*atan2(1,0);

# at N=k^2 polygon of 2k+1 sides R=k
# dX -> sin(2pi/(2k+1))*k
#    -> 2pi/(2k+1) * k
#    -> pi

use constant dx_minimum => - 2*atan2(1,0);  # -pi
use constant dx_maximum =>   2*atan2(1,0);  # +pi
use constant dy_minimum => - 2*atan2(1,0);
use constant dy_maximum =>   2*atan2(1,0);


#------------------------------------------------------------------------------
# sub _as_float {
#   my ($x) = @_;
#   if (ref $x) {
#     if ($x->isa('Math::BigInt')) {
#       return Math::BigFloat->new($x);
#     }
#     if ($x->isa('Math::BigRat')) {
#       return $x->as_float;
#     }
#   }
#   return $x;
# }

# Note: this is "use Math::BigFloat" not "require Math::BigFloat" because
# BigFloat 1.997 does some setups in its import() needed to tie-in to the
# BigInt back-end, or something.
use constant::defer _bigfloat => sub {
  eval "use Math::BigFloat; 1" or die $@;
  return "Math::BigFloat";
};

sub n_to_xy {
  my ($self, $n) = @_;
  if ($n < 0) {
    return;
  }
  my $two_pi = _TWO_PI();

  if (ref $n) {
    if ($n->isa('Math::BigInt')) {
      $n = _bigfloat()->new($n);
    }
    if ($n->isa('Math::BigRat')) {
      $n = $n->as_float;
    }
    if ($n->isa('Math::BigFloat')) {
      $two_pi = 2 * Math::BigFloat->bpi ($n->accuracy
                                         || $n->precision
                                         || $n->div_scale);
    }
  }

  my $r = sqrt($n);
  my $theta = $two_pi * ($r - int($r));  # 0 <= $theta < 2*pi
  return ($r * cos($theta),
          $r * sin($theta));

}

sub n_to_rsquared {
  my ($self, $n) = @_;
  if ($n < 0) { return undef; }
  return $n;  # exactly RSquared=$n
}

sub xy_to_n {
  my ($self, $x, $y) = @_;
  ### SacksSpiral xy_to_n(): "$x, $y"

  my $theta_frac = Math::PlanePath::MultipleRings::_xy_to_angle_frac($x,$y);
  ### assert: 0 <= $theta_frac && $theta_frac < 1

  # the nearest arc, integer
  my $s = floor (hypot($x,$y) - $theta_frac + 0.5);

  # the nearest N on the arc
  my $n = floor ($s*$s + $theta_frac * (2*$s + 1) + 0.5);

  # check within 0.5 radius
  my ($nx, $ny) = $self->n_to_xy($n);

  ### $theta_frac
  ### raw hypot: hypot($x,$y)
  ### $s
  ### $n
  ### hypot: hypot($nx-$x, $ny-$y)
  if (hypot($nx-$x,$ny-$y) <= 0.5) {
    return $n;
  } else {
    return undef;
  }
}

# r^2 = x^2 + y^2
# (r+1)^2 = r^2 + 2r + 1
# r < x+y
# (r+1)^2 < x^2+y^2 + x + y + 1
#         < (x+.5)^2 + (y+.5)^2 + 1
# (x+1)^2 + (y+1)^2 = x^2+y^2 + 2x+2y+2
#
# (x+1)^2 + (y+1)^2 - (r+1)^2
#   = x^2+y^2 + 2x+2y+2 - (r^2 + 2r + 1)
#   = x^2+y^2 + 2x+2y+2 - x^2-y^2 - 2*sqrt(x^2+y^2) - 1
#   = 2x+2y+1 - 2*sqrt(x^2+y^2)
#   >= 2x+2y+1 - 2*(x+y)
#   = 1
#
# (x+e)^2 + (y+e)^2 - (r+e)^2
#   = x^2+y^2 + 2xe+2ye + 2e^2 - (r^2 + 2re + e^2)
#   = x^2+y^2 + 2xe+2ye + 2e^2 - x^2-y^2 - 2*e*sqrt(x^2+y^2) - e^2
#   = 2xe+2ye + e^2 - 2*e*sqrt(x^2+y^2)
#   >= 2xe+2ye + e^2 - 2*e*(x+y)
#   = e^2 
#
# x+1,y+1 increases the radius by at least 1 thus pushing it to the outside
# of a ring.  Actually it's more, as much as sqrt(2)=1.4142 on the leading
# diagonal X=Y.  But the over-estimate is close enough for now.
# 

# r = hypot(xmin,ymin)
# Nlo = (r-1/2)^2
#     = r^2 - r + 1/4
#     >= x^2+y^2 - (x+y)    because x+y >= r
#     = x(x-1) + y(y-1)
#     >= floorx(floorx-1) + floory(floory-1)
# in integers if round down to x=0 then x*(x-1)=0 too, so not negative
#
# r = hypot(xmax,ymax)
# Nhi = (r+1/2)^2
#     = r^2 + r + 1/4
#     <= x^2+y^2 + (x+y) + 1
#     = x(x+1) + y(y+1) + 1
#     <= ceilx(ceilx+1) + ceily(ceily+1) + 1

# Note: this code shared by TheodorusSpiral.  If start using the polar angle
# for more accuracy here then unshare it first.
#
# not exact
sub rect_to_n_range {
  my ($self, $x1,$y1, $x2,$y2) = @_;
  ($x1,$y1, $x2,$y2) = _rect_to_radius_corners ($x1,$y1, $x2,$y2);

  ### $x_min
  ### $y_min
  ### N min: $x_min*($x_min-1) + $y_min*($y_min-1)

  ### $x_max
  ### $y_max
  ### N max: $x_max*($x_max+1) + $y_max*($y_max+1) + 1

  return ($x1*($x1-1) + $y1*($y1-1),
          $x2*($x2+1) + $y2*($y2+1) + 1);
}

#------------------------------------------------------------------------------
# generic

# $x1,$y1, $x2,$y2 is a rectangle.
# Return ($xmin,$ymin, $xmax,$ymax).
#
# The two points are respectively minimum and maximum radius from the
# origin, rounded down or up to integers.
#
# If the rectangle is entirely one quadrant then the points are two opposing
# corners.  But if an axis is crossed then the minimum is on that axis and
# if the origin is covered then the minimum is 0,0.
#
# Currently the return is abs() absolute values of the places.  Could change
# that if there was any significance to the quadrant containing the min/max
# corners.
#
sub _rect_to_radius_corners {
  my ($x1,$y1, $x2,$y2) = @_;

  ($x1,$x2) = ($x2,$x1) if $x1 > $x2;
  ($y1,$y2) = ($y2,$y1) if $y1 > $y2;

  return (int($x2 < 0 ? -$x2
              : $x1 > 0 ? $x1
              : 0),
          int($y2 < 0 ? -$y2
              : $y1 > 0 ? $y1
              : 0),

          max(_ceil(abs($x1)), _ceil(abs($x2))),
          max(_ceil(abs($y1)), _ceil(abs($y2))));
}

sub _ceil {
  my ($x) = @_;
  my $int = int($x);
  return ($x > $int ? $int+1 : $int);
}

# FIXME: prefer to stay in integers if possible
# return ($rlo,$rhi) which is the radial distance range found in the rectangle
sub _rect_to_radius_range {
  my ($x1,$y1, $x2,$y2) = @_;

  ($x1,$y1, $x2,$y2) = _rect_to_radius_corners ($x1,$y1, $x2,$y2);
  return (hypot($x1,$y1),
          hypot($x2,$y2));
}

1;
__END__

=for stopwords Archimedean ie pronic PlanePath Ryde Math-PlanePath XPM Euler's arctan Theodorus dX dY

=head1 NAME

Math::PlanePath::SacksSpiral -- circular spiral squaring each revolution

=head1 SYNOPSIS

 use Math::PlanePath::SacksSpiral;
 my $path = Math::PlanePath::SacksSpiral->new;
 my ($x, $y) = $path->n_to_xy (123);

=head1 DESCRIPTION

X<Sacks, Robert>X<Square numbers>The Sacks spiral by Robert Sacks is an
Archimedean spiral with points N placed on the spiral so the perfect squares
fall on a line going to the right.  Read more at

=over

L<http://www.numberspiral.com>

=back

An Archimedean spiral means each loop is a constant distance from the
preceding, in this case 1 unit.  The polar coordinates are

    R = sqrt(N)
    theta = sqrt(N) * 2pi

which comes out roughly as

                    18
          19   11        10  17
                     5
             
    20  12  6   2
                   0  1   4   9  16  25

                   3
      21   13   7        8
                             15   24
                    14
               22        23

The X,Y positions returned are fractional, except for the perfect squares on
the positive X axis at X=0,1,2,3,etc.  The perfect squares are the closest
points, at 1 unit apart.  Other points are a little further apart.

The arms going to the right like N=5,10,17,etc or N=8,15,24,etc are constant
offsets from the perfect squares, ie. S<d^2 + c> for positive or negative
integer c.  To the left the central arm N=2,6,12,20,etc is the
X<Pronic numbers>pronic numbers S<d^2 + d> = S<d*(d+1)>, half way between
the successive perfect squares.  Other arms going to the left are offsets
from that, ie. S<d*(d+1) + c> for integer c.

Euler's quadratic d^2+d+41 is one such arm going left.  Low values loop
around a few times before straightening out at about y=-127.  This quadratic
has relatively many primes and in a plot of primes on the spiral it can be
seen standing out from its surrounds.

Plotting various quadratic sequences of points can form attractive patterns.
For example the X<Triangular numbers>triangular numbers k*(k+1)/2 come out
as spiral arcs going clockwise and anti-clockwise.

See F<examples/sacks-xpm.pl> in the Math-PlanePath sources for a complete
program plotting the spiral points to an XPM image.

=head1 FUNCTIONS

See L<Math::PlanePath/FUNCTIONS> for behaviour common to all path classes.

=over 4

=item C<$path = Math::PlanePath::SacksSpiral-E<gt>new ()>

Create and return a new path object.

=item C<($x,$y) = $path-E<gt>n_to_xy ($n)>

Return the X,Y coordinates of point number C<$n> on the path.

C<$n> can be any value C<$n E<gt>= 0> and fractions give positions on the
spiral in between the integer points.

For C<$n < 0> the return is an empty list, it being considered there are no
negative points in the spiral.

=item C<$rsquared = $path-E<gt>n_to_rsquared ($n)>

Return the radial distance R^2 of point C<$n>, or C<undef> if there's
no point C<$n>.  This is simply C<$n> itself, since R=sqrt(N).

=item C<$n = $path-E<gt>xy_to_n ($x,$y)>

Return an integer point number for coordinates C<$x,$y>.  Each integer N
is considered the centre of a circle of diameter 1 and an C<$x,$y> within
that circle returns N.

The unit spacing of the spiral means those circles don't overlap, but they
also don't cover the plane and if C<$x,$y> is not within one then the
return is C<undef>.

=back

=head2 Descriptive Methods

=over

=item C<$dx = $path-E<gt>dx_minimum()>

=item C<$dx = $path-E<gt>dx_maximum()>

=item C<$dy = $path-E<gt>dy_minimum()>

=item C<$dy = $path-E<gt>dy_maximum()>

dX and dY have minimum -pi=-3.14159 and maximum pi=3.14159.  The loop
beginning at N=2^k is approximately a polygon of 2k+1 many sides and radius
R=k.  Each side is therefore

    side = sin(2pi/(2k+1)) * k
        -> 2pi/(2k+1) * k
        -> pi

=item C<$str = $path-E<gt>figure ()>

Return "circle".

=back

=head1 FORMULAS

=head2 Rectangle to N Range

R=sqrt(N) here is the same as in the C<TheodorusSpiral> and the code is
shared here.  See L<Math::PlanePath::TheodorusSpiral/Rectangle to N Range>.

The accuracy could be improved here by taking into account the polar angle
of the corners which are candidates for the maximum radius.  On the X axis
the stripes of N are from X-0.5 to X+0.5, but up on the Y axis it's 0.25
further out at Y-0.25 to Y+0.75.  The stripe the corner falls in can thus be
biased by theta expressed as a fraction 0 to 1 around the plane.

An exact theta 0 to 1 would require an arctan, but approximations 0, 0.25,
0.5, 0.75 from the quadrants, or eighths of the plane by XE<gt>Y etc
diagonals.  As noted for the Theodorus spiral the over-estimate from
ignoring the angle is at worst R many points, which corresponds to a full
loop here.  Using the angle would reduce that to 1/4 or 1/8 etc of a loop.

=head1 SEE ALSO

L<Math::PlanePath>,
L<Math::PlanePath::PyramidRows>,
L<Math::PlanePath::ArchimedeanChords>,
L<Math::PlanePath::TheodorusSpiral>,
L<Math::PlanePath::VogelFloret>

=head1 HOME PAGE

L<http://user42.tuxfamily.org/math-planepath/index.html>

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
