# Copyright 2011, 2012, 2013 Kevin Ryde

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


# Also possible would be circle involute spiral, unrolling string around
# centre of circumference 1, but is only very slightly different radius from
# an Archimedean spiral.


package Math::PlanePath::ArchimedeanChords;
use 5.004;
use strict;
use Math::Libm 'hypot', 'asinh';
use POSIX 'ceil';
#use List::Util 'min', 'max';
*min = \&Math::PlanePath::_min;
*max = \&Math::PlanePath::_max;

use vars '$VERSION', '@ISA';
$VERSION = 111;
use Math::PlanePath;
@ISA = ('Math::PlanePath');

use Math::PlanePath::Base::Generic
  'is_infinite',
  'round_nearest';
use Math::PlanePath::MultipleRings;

# uncomment this to run the ### lines
# use Smart::Comments;


use constant figure => 'circle';
use constant n_start => 0;
use constant gcdxy_maximum => 1;
use constant dx_minimum => -1; # infimum when straight
use constant dx_maximum => 1;  # at N=0
use constant dy_minimum => -1;
use constant dy_maximum => 1;
use constant dsumxy_minimum => -sqrt(2); # supremum when diagonal
use constant dsumxy_maximum => sqrt(2);
use constant ddiffxy_minimum => -sqrt(2); # supremum when diagonal
use constant ddiffxy_maximum => sqrt(2);


#------------------------------------------------------------------------------

use constant 1.02 _PI => 2*atan2(1,0);

# Starting at polar angle position t in radians,
#
#     r = t / 2pi
#
#     x = r * cos(t) = t * cos(t) / 2pi
#     y = r * sin(t) = t * sin(t) / 2pi
#
# Want a polar angle amount u to move by a chord distance of 1.  Hypot
# square distance from t to t+u is
#
#     dist(u) =   ( (t+u)/2pi*cos(t+u) - t/2pi*cos(t) )^2     # X
#               + ( (t+u)/2pi*sin(t+u) - t/2pi*sin(t) )^2     # Y
#           = [  (t+u)^2*cos^2(t+u) - 2*(t+u)*t*cos(t+u)*cos(t) + t^2*cos^2(t)
#              + (t+u)^2*sin^2(t+u) - 2*(t+u)*t*sin(t+u)*sin(t) + t^2*sin^2(t)
#             ] / (4*pi^2)
#
# and from sin^2 + cos^2 = 1
# and addition cosA*cosB + sinA*sinB = cos(A-B)
#
#           = [  (t+u)^2            - 2*(t+u)*t*cos((t+u)-t)    + t^2 ] /4pi^2
#           = [ (t+u)^2 + t^2 - 2*t*(t+u)*cos(u) ] / (4*pi^2)
#
# then double angle cos(u) = 1 - 2*sin^2(u/2) to go to the sine since if u
# is small then cos(u) near 1.0 might lose accuracy
#
#     dist(u) = [(t+u)^2 + t^2 - 2*t*(t+u)*(1 - 2*sin^2(u/2))] / (4*pi^2)
#             = [(t+u)^2 + t^2 - 2*t*(t+u) + 2*t*(t+u)*2*sin^2(u/2)] / (4*pi^2)
#             = [((t+u) - t)^2 + 4*t*(t+u)*sin^2(u/2)] / (4*pi^2)
#             = [ u^2 + 4*t*(t+u)*sin^2(u/2) ] / (4*pi^2)
#
# Seek d(u) = 1 by letting f(u)=4*pi^2*(d(u)-1) and seeking f(u)=0
#
#     f(u) = u^2 + 4*t*(t+u)*sin^2(u/2) - 4*pi^2
#
# Derivative f'(u) for the slope, starting from the cosine form,
#
#     f(u)  = (t+u)^2 + t^2 - 2*t*(t+u)*cos(u) - 4*pi^2
#
#     f'(u) = 2*(t+u) - 2*t*[ cos(u) - (t+u)*sin(u) ]
#           = 2*(t+u) - 2*t*[ 1 - 2*sin^2(u/2) - (t+u)*sin(u) ]
#           = 2*t + 2*u - 2*t + 2*t*2*sin^2(u/2) + 2*t*(t+u)*sin(u)
#           = 2*[ u + 2*t*sin^2(u/2) + t*(t+u)*sin(u) ]
#           = 2*[ u + t * [2*sin^2(u/2) + (t+u)*sin(u) ] ]
#
# Newton's method
#                           */    <- f(x) high
#                          */|
#                        * / |
#                      *  /  |
#          ---------*------------------
#                        +---+  <- subtract
#
#      f(x) / sub = f'(x)
#      sub = f(x) / f'(x)
#
#
# _chord_angle_inc() takes $t is a polar angle around the Archimedean spiral.
# Returns an increment polar angle $u which may be added to $t to move around
# the spiral by a chord length 1 unit.
#
# The loop is Newton's method, $f=f(u), $slope=f'(u) so $u-$f/$slope is a
# better $u, ie. f($u) closer to 0.  Stop when the subtract becomes small,
# usually only about 3 iterations.
#
sub _chord_angle_inc {
  my ($t) = @_;
  # ### _chord_angle_inc(): $t

  my $u = 2*_PI/$t; # estimate

  foreach (0 .. 10) {
    my $shu = sin($u/2); $shu *= $shu;   # sin^2(u/2)
    my $tu = ($t+$u);

    my $f = $u*$u + 4*$t*$tu*$shu - 4*_PI*_PI;
    my $slope = 2 * ( $u + $t*(2*$shu + $tu*sin($u)));

    # unsimplified versions ...
    # $f = ($t+$u)**2 + $t**2 - 2*$t*($t+$u)*cos($u) - 4*_PI*_PI;
    # $slope = 2*($t+$u) - 2*$t*( cos($u) - ($t+$u)*sin($u) );

    my $sub = $f/$slope;
    $u -= $sub;

    # printf ("f=%.6f slope=%.6f sub=%.20f u=%.6f\n", $f, $slope, $sub, $u);
    last if (abs($sub) < 1e-15);
  }
  # printf ("return u=%.6f\n", $u);
  return $u;
}

use constant 1.02; # for leading underscore
use constant _SAVE => 500;

my @save_n = (1);
my @save_t = (2*_PI);
my $next_save = $save_n[0] + _SAVE;

sub new {
  ### ArchimedeanChords new() ...
  return shift->SUPER::new (i => $save_n[0],
                            t => $save_t[0],
                            @_);
}

sub n_to_xy {
  my ($self, $n) = @_;

  if ($n < 0) { return; }
  if (is_infinite($n)) { return ($n,$n); }

  if ($n <= 1) {
    return ($n, 0);  # exactly Y=0
  }

  {
    # ENHANCE-ME: look at the N+1 position for the frac directly, without
    # the full call for N+1
    my $int = int($n);
    if ($n != $int) {
      my $frac = $n - $int;  # inherit possible BigFloat/BigRat
      my ($x1,$y1) = $self->n_to_xy($int);
      my ($x2,$y2) = $self->n_to_xy($int+1);
      my $dx = $x2-$x1;
      my $dy = $y2-$y1;
      return ($frac*$dx + $x1, $frac*$dy + $y1);
    }
  }

  my $i = $self->{'i'};
  my $t = $self->{'t'};

  if ($i > $n) {
    my $pos = min ($#save_n, int (($n - $save_n[0]) / _SAVE));
    $i = $save_n[$pos];
    $t = $save_t[$pos];
    ### resume: "$i  t=$t"
  }

  while ($i < $n) {
    $t += _chord_angle_inc($t);
    if (++$i == $next_save) {
      push @save_n, $i;
      push @save_t, $t;
      $next_save += _SAVE;
    }
  }

  $self->{'i'} = $i;
  $self->{'t'} = $t;

  my $r = $t * (1 / (2*_PI));
  return ($r*cos($t),
          $r*sin($t));
}

sub _xy_to_nearest_r {
  my ($x, $y) = @_;
  my $frac = Math::PlanePath::MultipleRings::_xy_to_angle_frac($x,$y);
  ### assert: 0 <= $frac && $frac < 1

  # if $frac > 0.5 then 0.5-$frac is negative and int() rounds towards zero
  # giving $r==$frac
  return int(hypot($x,$y) + 0.5 - $frac) + $frac;
}

sub xy_to_n {
  my ($self, $x, $y) = @_;
  ### ArchimedeanChords xy_to_n(): "$x, $y"

  my $r = _xy_to_nearest_r($x,$y);
  my $r_limit = 1.001 * $r;

  ### hypot: hypot($x,$y)
  ### $r
  ### $r_limit
  ### save_t: "end index=$#save_t  save_t[0]=".($save_t[0]//'undef')

  if (is_infinite($r_limit)) {
    ### infinite range, r inf or too big ...
    return undef;
  }

  my $theta = 0.999 * 2*_PI*$r;
  my $n_lo = 0;
  foreach my $i (1 .. $#save_t) {
    if ($save_t[$i] > $theta) {
      $n_lo = $save_n[$i-1];
      if ($n_lo == 1) { $n_lo = 0; } # for finding X=0,Y=0
      last;
    }
  }
  ### $n_lo

  # loop with for(;;) since $n_lo..$n_hi limited to IV range
  for (my $n = $n_lo; ; $n += 1) {
    my ($nx,$ny) = $self->n_to_xy($n);
    # #### $n
    # #### $nx
    # #### $ny
    # #### hypot: hypot ($x-$nx,$y-$ny)
    if (hypot($x-$nx,$y-$ny) <= 0.5) {
      ### hypot in range ...
      return $n;
    }
    if (hypot($nx,$ny) >= $r_limit) {
      last;
    }
  }

  ### n not found ...
  return undef;
}

  # int (max (0, int(_PI*$r2) - 4*$r));
  #
  #  my $r2 = $r * $r;
  #  my $n_lo =  int (max (0, int(_PI*$r2) - 4*$r));
  #  my $n_hi = $n_lo + 7*$r + 2;
  # ### $r2
  # $n_lo == $n_lo-1 ||




# x,y has radius hypot(x,y), then want the next higher spiral arc which is r
# >= hypot(x,y)+0.5, with the 0.5 being the width of the circle figure on
# the spiral.
#
# The polar angle of x,y is a=atan2(y,x) and frac=a/2pi is the extra away
# from an integer radius for the spiral.  So seek integer k with k+a/2pi >=
# h with h=hypot(x,y)+0.5.
#
#     k + a/2pi >= h
#     k >= h-a/2pi
#     k = ceil(h-a/2pi)
#       = ceil(hypot(x,y) + 0.5 - atan2(y,x)/2pi)
#
#
# circle radius i has circumference 2*pi*i and at most that many N on it
# rectangle corner at radius Rcorner = hypot(x,y)
#
# sum i=1 to i=Rlimit of 2*pi*i = 2*pi/2 * Rlimit*(Rlimit+1)
#                               = pi * Rlimit*(Rlimit+1)
# is an upper bound, though a fairly slack one
#
#
# cf. arc length along the spiral r=a*theta with a=1/2pi
#     arclength = (1/2) * a * (theta*sqrt(1+theta^2) + asinh(theta))
#               = (1/4*pi) * (theta*sqrt(1+theta^2) + asinh(theta))
# and theta = 2*pi*r
#               = (1/4*pi) * (4*pi^2*r^2 * sqrt(1+1/theta^2) + asinh(theta))
#               = pi * (r^2 * sqrt(1+1/r^2) + asinh(theta)/(4*pi^2))
#
# and to compare to the circles formula
#
#               = pi * (r*(r+1) * r/(r+1) * sqrt(1+1/r^2)
#                       + asinh(theta)/(4*pi^2))
#
# so it's smaller hence better upper bound.  Only a little smaller than the
# squaring once get to 100 loops or so.
#
#
# not exact
sub rect_to_n_range {
  my ($self, $x1,$y1, $x2,$y2) = @_;
  ### rect_to_n_range() ...

  my $rhi = 0;
  foreach my $x ($x1, $x2) {
    foreach my $y ($y1, $y2) {
      my $frac = atan2($y,$x) / (2*_PI);  # -0.5 <= $frac <= 0.5
      $frac += ($frac < 0);                #    0 <= $frac < 1
      $rhi = max ($rhi, ceil(hypot($x,$y)+0.5 - $frac) + $frac);
    }
  }
  ### $rhi

  # arc length pi * (r^2 * sqrt(1+1/r^2) + asinh(theta)/(4*pi^2))
  #          = pi*r^2*sqrt(1+1/r^2) + asinh(theta)/4pi
  my $rhi2 = $rhi*$rhi;
  return (0,
          ceil (_PI * $rhi2 * sqrt(1+1/$rhi2)
                + asinh(2*_PI*$rhi) / (4*_PI)));


  # # each loop begins at N = pi*k^2 - 2 or thereabouts
  # return (0,
  #         int(_PI*$rhi*($rhi+1) + 1));
}

1;
__END__

    # my $slope = 2*($t + (-$c1-$s1*$t)*cos($t) + ($c1*$t-$s1)*sin($t));
    # my $dist = ( ($t*cos($t) - $c1) ** 2
    #           + ($t*sin($t) - $s1) ** 2
    #           - 4*_PI*_PI );
    # my $slope = (2*($t*cos($t)-$c1)*(cos($t) - $t*sin($t))
    #          + 2*($t*sin($t)-$s1)*(sin($t) + $t*cos($t)));
    # my $c1 = $t1 * cos($t1);
    # my $s1 = $t1 * sin($t1);
    # my $c1_2 = $c1*2;
    # my $s1_2 = $s1*2;
    # my $t = $t1 + 2*_PI/$t1; # estimate
    # my $ct = cos($t);
    # my $st = sin($t);
    # my $dist = (($t - $ct*$c1_2 - $st*$s1_2) * $t + $t1sqm);
    # my $slope = 2 * (($t*$ct - $c1) * ($ct - $t*$st)
    #                  + ($t*$st - $s1) * ($st + $t*$ct));
    #
    # my $sub = $dist/$slope;
    # $t -= $sub;

# use constant _A => 1 / (2*_PI);


# my @radius = (0, 1);

  # # my $theta = _inverse($n);
  # # my $r = _A * $theta;
  # # return ($r * cos($theta),
  # #         $r * sin($theta));
  #
  #
  # #   $n = floor($n);
  # #
  # #   for (my $i = scalar(@radius); $i <= $n; $i++) {
  # #     my $prev = $radius[$i-1];
  # #     # my $step = 8 * asin (.25/4 / $prev) / pi();
  # #     my $step = (.5 / pi()) / $prev;
  # #     $radius[$i] = $prev + $step;
  # #   }
  # #
  # #   my $r = $radius[$n];
  # #   my $theta = 2 * pi() * ($r - int($r));  # radians 0 to 2*pi
  # #   return ($r * cos($theta),
  # #           $r * sin($theta));
# sub _arc_length {
#   my ($theta) = @_;
#   my $hyp = hypot(1,$theta);
#   return 0.5 * _A * ($theta*$hyp + asinh($theta));
# }
#
# # upper bound $hyp >= $theta
# #     a/2 * $theta * $theta
# # so theta = sqrt (2/_A * $length)
# #
# # lower bound $hyp <= $theta+1, log(x)<=x
# #     length <= a/2 * ($theta * ($theta+1))^2
# #     2/a * length <= (2*$theta * $theta)^2
# # so theta >= sqrt (1/(2*_A) * $length)
# #
# sub _inverse {
#   my ($length) = @_;
#   my $lo_theta = sqrt (1/(2*_A) * $length);
#   my $hi_theta = sqrt ((2/_A) * $length);
#   my $lo_length = _arc_length($lo_theta);
#   my $hi_length = _arc_length($hi_theta);
#   #### $length
#   #### $lo_theta
#   #### $hi_theta
#   #### $lo_length
#   #### $hi_length
#   die if $lo_length > $length;
#   die if $hi_length < $length;
#   my $m_theta;
#   for (;;) {
#     $m_theta = ($hi_theta + $lo_theta) / 2;
#     last if ($hi_length - $lo_length) < 0.000001;
#     my $m_length = _arc_length($m_theta);
#     if ($m_length < $length) {
#       $lo_theta = $m_theta;
#       $lo_length = $m_length;
#     } else {
#       $hi_theta = $m_theta;
#       $hi_length = $m_length;
#     }
#   }
#   return $m_theta;
# }


=for stopwords Archimedean Ryde ie cartesian Math-PlanePath arcsin

=head1 NAME

Math::PlanePath::ArchimedeanChords -- radial spiral chords

=head1 SYNOPSIS

 use Math::PlanePath::ArchimedeanChords;
 my $path = Math::PlanePath::ArchimedeanChords->new;
 my ($x, $y) = $path->n_to_xy (123);

=head1 DESCRIPTION

This path puts points at unit chord steps along an Archimedean spiral.  The
spiral goes outwards by 1 unit each revolution and the points are spaced 1
apart.

    R = theta/(2*pi)

The result is roughly

                         31              
                   32          30         ...                3
             33                   29
                      14
       34       15          13          28    50             2
                                  12
             16        3
    35                       2             27    49          1
                    4                11
          17
    36           5        0     1          26    48     <- Y=0
                                     10
          18
    37              6                      25    47         -1
                                   9
             19        7     8          24    46
       38                                                   -2
                20                23
          39          21    22             45
                                                            -3
                40                   44
                   41    42    43


                          ^
       -3    -2    -1    X=0    1     2     3     4

X,Y positions returned are fractional.  Each revolution is about 2*pi longer
than the previous, so the effect is a kind of 6.28 increment looping.

Because the spacing is by unit chords, adjacent unit circles centred on each
N position touch but don't overlap.  The spiral spacing of 1 unit per
revolution means they don't overlap radially either.

The unit chords here are a little like the C<TheodorusSpiral>.  But the
C<TheodorusSpiral> goes by unit steps at a fixed right-angle and
approximates an Archimedean spiral (of 3.14 radial spacing).  Whereas this
C<ArchimedeanChords> is an actual Archimedean spiral (of radial spacing 1),
with unit steps angling along that.

=head1 FUNCTIONS

See L<Math::PlanePath/FUNCTIONS> for behaviour common to all path classes.

=over 4

=item C<$path = Math::PlanePath::ArchimedeanChords-E<gt>new ()>

Create and return a new path object.

=item C<($x,$y) = $path-E<gt>n_to_xy ($n)>

Return the X,Y coordinates of point number C<$n> on the path.

C<$n> can be any value C<$n E<gt>= 0> and fractions give positions on the
chord between the integer points (ie. straight line between the points).
C<$n==0> is the origin 0,0.

For C<$n < 0> the return is an empty list, it being considered there are no
negative points in the spiral.

=item C<$n = $path-E<gt>xy_to_n ($x,$y)>

Return an integer point number for coordinates C<$x,$y>.  Each integer N
is considered the centre of a circle of diameter 1 and an C<$x,$y> within
that circle returns N.

The unit spacing of the spiral means those circles don't overlap, but they
also don't cover the plane and if C<$x,$y> is not within one then the
return is C<undef>.

The current implementation is a bit slow.

=item C<$n = $path-E<gt>n_start ()>

Return 0, the first C<$n> on the path.

=item C<$str = $path-E<gt>figure ()>

Return "circle".

=back

=head1 FORMULAS

=head2 N to X,Y

The current code keeps a position as a polar angle t and calculates an
increment u needed to move along by a unit chord.  If dist(u) is the
straight-line distance between t and t+u, then squared is the hypotenuse

    dist^2(u) =   ((t+u)/2pi*cos(t+u) - t/2pi*cos(t))^2     # X
                + ((t+u)/2pi*sin(t+u) - t/2pi*sin(t))^2     # Y

which simplifies to

    dist^2(u) = [ (t+u)^2 + t^2 - 2*t*(t+u)*cos(u) ] / (4*pi^2)

Switch from cos to sin using the half angle cos(u) = 1 - 2*sin^2(u/2) in
case if u is small then the cos(u) near 1.0 might lose floating point
accuracy, and also as a slight simplification,

    dist^2(u) = [ u^2 + 4*t*(t+u)*sin^2(u/2) ] / (4*pi^2)

Then want the u which has dist(u)=1 for a unit chord.  The u*sin(u) part
probably doesn't have a good closed form inverse, so the current code is a
Newton/Raphson iteration on f(u) = dist^2(u)-1, seeking f(u)=0

    f(u) = u^2 + 4*t*(t+u)*sin^2(u/2) - 4*pi^2

Derivative f'(u) for the slope from the cos form is

    f'(u) = 2*(t+u) - 2*t*[ cos(u) - (t+u)*sin(u) ]

And again switching from cos to sin in case u is small,

    f'(u) = 2*[ u + t*[2*sin^2(u/2) + (t+u)*sin(u)] ]

=head2 X,Y to N

A given x,y point is at a fraction of a revolution

    frac = atan2(y,x) / 2pi     # -.5 <= frac <= .5
    frac += (frac < 0)          # 0 <= frac < 1

And the nearest spiral arm measured radially from x,y is then

    r = int(hypot(x,y) + .5 - frac) + frac

Perl's C<atan2> is the same as the C library and gives -pi E<lt>= angle
E<lt>= pi, hence allowing for fracE<lt>0.  It may also be "unspecified" for
x=0,y=0, and give +/-pi for x=negzero, which has to be a special case so 0,0
gives r=0.  The C<int> rounds towards zero, so frac>.5 ends up as r=0.

So the N point just before or after that spiral position may cover the x,y,
but how many N chords it takes to get around to there is 's not so easily
calculated.

The current code looks in saved C<n_to_xy()> positions for an N below the
target, and searches up from there until past the target and thus not
covering x,y.  With C<n_to_xy()> points saved 500 apart this means searching
somewhere between 1 and 500 points.

One possibility for calculating a lower bound for N, instead of the saved
positions, and both for C<xy_to_n()> and C<rect_to_n_range()>, would be to
add up chords in circles.  A circle of radius k fits pi/arcsin(1/2k) many
unit chords, so

             k=floor(r)     pi
    total = sum         ------------
             k=0        arcsin(1/2k)

and this is less than the chords along the spiral.  Is there a good
polynomial over-estimate of arcsin, to become an under-estimate total,
without giving away so much?

=head2 Rectangle to N Range

For the C<rect_to_n_range()> upper bound, the current code takes the arc
length along with spiral with the usual formula

    arc = 1/4pi * (theta*sqrt(1+theta^2) + asinh(theta))

Written in terms of the r radius (theta = 2pi*r) as calculated from the
biggest of the rectangle x,y corners,

    arc = pi*r^2*sqrt(1+1/r^2) + asinh(2pi*r)/4pi

The arc length is longer than chords, so N=ceil(arc) is an upper bound for
the N range.

An upper bound can also be calculated simply from the circumferences of
circles 1 to r, since a spiral loop from radius k to k+1 is shorter than a
circle of radius k.

             k=ceil(r)
    total = sum         2pi*k
             k=1

          = pi*r*(r+1)

This is bigger than the arc length, thus a poorer upper bound, but an easier
calculation.  (Incidentally, for smallish r have arc length E<lt>=
pi*(r^2+1) which is a tighter bound and an easy calculation, but it only
holds up to somewhere around r=10^7.)

=head1 SEE ALSO

L<Math::PlanePath>,
L<Math::PlanePath::TheodorusSpiral>,
L<Math::PlanePath::SacksSpiral>

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
