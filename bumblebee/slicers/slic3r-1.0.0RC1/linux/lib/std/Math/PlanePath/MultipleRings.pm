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



# math-image --path=MultipleRings --lines
#
# math-image --wx --path=MultipleRings,ring_shape=polygon,step=5  --scale=50 --figure=ring --all

#
# FIXME: $y equal across bottom side centre ?


package Math::PlanePath::MultipleRings;
use 5.004;
use strict;
use Carp;
#use List::Util 'min','max';
*min = \&Math::PlanePath::_min;
*max = \&Math::PlanePath::_max;

# Math::Trig has asin_real() too, but it just runs the blob of code in
# Math::Complex -- prefer libm
use Math::Libm 'asin', 'hypot';

use vars '$VERSION', '@ISA';
@ISA = ('Math::PlanePath');
use Math::PlanePath;
$VERSION = 111;

use Math::PlanePath::Base::Generic
  'is_infinite';
use Math::PlanePath::SacksSpiral;

# uncomment this to run the ### lines
# use Smart::Comments;


use constant 1.02; # for leading underscore
use constant _PI => 2*atan2(1,0);

use constant figure => 'circle';
use constant n_frac_discontinuity => 0;
use constant gcdxy_minimum => 0;

use constant parameter_info_array =>
  [{ name        => 'step',
     display     => 'Step',
     share_key   => 'step_6_min3',
     type        => 'integer',
     minimum     => 0,
     default     => 6,
     width       => 3,
     description => 'How much longer each ring is than the preceding.',
   },

   { name        => 'ring_shape',
     display     => 'Ring Shape',
     type        => 'enum',
     default     => 'circle',
     choices     => ['circle','polygon'],
     choices_display => ['Circle','Polygon'],
     description     => 'The shape of each ring, either a circle or a polygon of "step" many sides.',
   },
  ];

#------------------------------------------------------------------------------
# Electricity transmission cable in sixes, with one at centre ?
#    7 poppy
#    19 hyacinth
#    37 marigold
#    61 cowslip
#    127 bluebonnet

# An n-gon of points many vertices has each angle
#     alpha = 2*pi/points
# The radius r to a vertex, using a line perpendicular to the line segment
#     sin(alpha/2) = (1/2)/r
#     r = 0.5 / sin(pi/points)
# And with points = d*step, starting from d=1
#     r = 0.5 / sin(pi/(d*step))

# step==0 is a straight line y==0 x=0,1,2,..., anything else whole plane
sub x_negative {
  my ($self) = @_;
  return ($self->{'step'} > 0);
}
*y_negative = \&x_negative;

sub y_maximum {
  my ($self) = @_;
  return ($self->{'step'} == 0 ? 0  # step=0 always Y=0
          : undef);
}

sub sumxy_minimum {
  my ($self) = @_;
  return ($self->{'step'} == 0 ? 0 : undef);
}
sub sumabsxy_minimum {
  my ($self) = @_;
  # first point N=1 innermost ring
  my ($x,$y) = $self->n_to_xy($self->n_start);
  return $x;
}
*diffxy_minimum = \&sumxy_minimum;

# step=0 X=0,Y=0 AbsDiff=0
# step=3 N=88 X=Y=5.3579957587697 ring of 24 is a multiple of 8

sub rsquared_minimum {
  my ($self) = @_;
  my $step = $self->{'step'};
  if ($step <= 1) {
    # step=0 along X axis starting X=0,Y=0
    # step=1 start at origin
    return 0;
  }

  # step=3  *--___        
  # circle  |     --__         o         0.5/r = sin60 = sqrt(3)/2
  #         |   o   __*      / | \       r = 1/sqrt(3)
  #         |  ___--       /   |   \     r^2 = 1/3
  #         *--           *---------*
  #                              1/2
  # polygon
  #          o         0.5/r = sin60 = sqrt(3)/2
  #        / | \       r = 1/sqrt(3)                 
  #      /   |   \     r^2 = 1/3                
  #     *---------*                             
  #            1/2                              
  # 
  if ($step == 3) {
    return ($self->{'ring_shape'} eq 'polygon' ? 3/4 : 1/3);
  }
  if ($step == 4) {
    # radius = sqrt(2)/2, rsquared=1/2
    return 0.5;
  }

  # _numsides_to_r() returns 1, no need for a special case here
  # if ($step == 6) {
  #   # hexagon
  #   return 1;
  # }

  my $r;
  if ($step >= 6 || $self->{'ring_shape'} eq 'polygon') {
    $r = _numsides_to_r($step,_PI);
  } else {
    $r = $self->{'base_r'} + 1;
  }
  return $r*$r;
}


#------------------------------------------------------------------------------
# dx_minimum() etc

# step <= 6
# R=base_r+d
# theta = 2*$n * $pi / ($d * $step)
#       = 2pi/(d*step)
# dX -> R*sin(theta)
#    -> R*theta
#     = (base_r+d)*2pi/(d*step)
#    -> 2pi/step
#
# step=5 across first ring
# N=6 at X=base_r+2, Y=0
# N=5 at R=base_r+1 theta = 2pi/5
#   X=(base_r+1)*cos(theta)
#   dX = base_r+2 - (base_r+1)*cos(theta)
#
# step=6 across first ring
# base_r = 0.5/sin(_PI/6) - 1
#        = 0.5/0.5 - 1
#        = 0
# N=7 at X=base_r+2, Y=0
# N=6 at R=base_r+1 theta = 2pi/6
#   X=(base_r+1)*cos(theta)
#   dX = base_r+2 - (base_r+1)*cos(theta)
#      = base_r+2 - (base_r+1)*0.5
#      = 1.5*base_r + 1.5
#      = 1.5
#
# step > 6
# R = 0.5 / sin($pi / ($d*$step))
# diff = 0.5 / sin($pi / ($d*$step)) - 0.5 / sin($pi / (($d-1)*$step))
#     -> 0.5 / ($pi / ($d*$step)) - 0.5 / ($pi / (($d-1)*$step))
#      = 0.5 * ($d*$step) / $pi - 0.5 * (($d-1)*$step) / $pi
#      = step*0.5/pi * ($d - ($d-1))
#      = step*0.5/pi
# and extra from N=step to N=step+1
#     * (1-cos(2pi/step))
#
sub dx_minimum {
  my ($self) = @_;
  if ($self->{'step'} == 0) {
    return 1;   # horizontal only
  }

  if ($self->{'step'} > 6) {
    return -1; # supremum, unless polygon and step even
  }
  if ($self->{'ring_shape'} eq 'polygon') {
    # step=3,4,5
    return (-2*_PI()) / $self->{'step'};
  } else {
    return (-2*_PI()) / $self->{'step'};
  }
}

sub dx_maximum {
  my ($self) = @_;
  return ($self->{'step'} == 0
          ? 1   # horizontal only

          : $self->{'step'} == 5
          ? $self->{'base_r'}+2 - ($self->{'base_r'}+1)*cos(2*_PI()/5)

          : $self->{'step'} == 6
          ? 1.5

          : $self->{'step'} <= 6
          ? (2*_PI()) / $self->{'step'}

          # step > 6, between rings
          : (0.5/_PI()) * $self->{'step'}
          * (2-cos(2*_PI()/$self->{'step'})));
}

sub dy_minimum {
  my ($self) = @_;
  return ($self->{'step'} == 0    ? 0    # horizontal only
          : $self->{'step'} <= 6  ? (-2*_PI) / $self->{'step'}
          :                         -1); # supremum
}
sub dy_maximum {
  my ($self) = @_;
  return ($self->{'step'} == 0    ? 0    # horizontal only
          : $self->{'step'} <= 6  ? (2*_PI) / $self->{'step'}
          :                         1); # supremum
}

sub absdx_minimum {
  my ($self) = @_;
  my $step = $self->{'step'};
  if ($step == 0) {
    return 1;    # horizontal dX=1 always
  }
  if ($self->{'ring_shape'} eq 'polygon') {
    if ($step % 2) {
      return 0;  # polygons with odd num sides have left vertical dX=0
    } else {
      return sin(_PI/2 /$step);
    }

    # if ($self->{'step'} % 2 == 1) {
    #
    #   return 0;
    # } else {
    #   return abs($self->dx_minimum);
    # }
  }
  return 0;
}
sub absdy_minimum {
  my ($self) = @_;
  my $step = $self->{'step'};
  if ($step == 0) {
    return 0;    # horizontal dX=1 always
  }
  if ($self->{'ring_shape'} eq 'polygon') {
    if ($step == 3) {
      return 0.5;  # sin(30 degrees) innermost polygon
    }
    my $frac = ($step+2) % 4;
    if ($frac == 3) { $frac = 1; }
    return sin(_PI/2 * $frac/$step);
  }
  return 0;
}

sub dsumxy_minimum {
  my ($self) = @_;
  return ($self->{'step'} == 0
          ? 1    # horizontal only
          : -1); # infimum
}
use constant dsumxy_maximum => 1;

# FIXME: for step=1 is there a supremum at 9 or thereabouts?
# and for other step<6 too?
# 2*dXmax * sqrt(2) ?
sub ddiffxy_minimum {
  my ($self) = @_;
  return ($self->{'step'} == 0  ? 1     # horizontal only
          : $self->{'step'} <= 6 ? $self->dx_minimum * sqrt(2)
          : -1); # infimum
}
sub ddiffxy_maximum {
  my ($self) = @_;
  return ($self->{'step'} == 0   ? 1     # horizontal only
          : $self->{'step'} <= 6 ? $self->dx_maximum * sqrt(2)
          : 1); # supremum
}

#------------------------------------------------------------------------------
# dir_maximum_dxdy()

# polygon step many sides
#   start at vertical angle 1/4 plus 0.5/step, then k*1/step each side
#   a = 1/4 + (k+1/2)/step
#     = (1 + 4(k+1/2)/step) / 4
#     = ((4*k+2)/step + 1) / 4
#
# maximum want 1 > a >= 1-1/step
#   1/4 + (k+1/2)/step >= 1-1/step
#   (k+1/2)/step >= 3/4-1/step
#   k+1/2 >= 3*step/4-1
#   k >= 3*step/4-3/2
#   k >= (3*step-6)/4
#   k = ceil((3*step-6)/4)
#     = floor((3*step-6)/4 + 3/4)
#     = floor((3*step-3)/4)
# high side
#   1/4 + (k+1/2)/step < 1
#   (k+1/2)/step < 3/4
#   k+1/2 < 3*step/4
#   k < (3*step-2)/4
#   k = floor((3*step-2)/4 - 1/4)
#     = floor((3*step-3)/4)
#
# so
#   a = 1/4 + (floor((3*step-3)/4) + 1/2)/step
#     = (1 + 4*(floor((3*step-3)/4) + 1/2)/step) / 4
#     = ((floor((3*step-3)/4)*4 + 2)/step + 1) / 4
# step=4   a = 7/8
# step=5   a = 19/20
# step=6   a = 5/6
# step=7   a = 25/28
# step=8   a = 15/16
# step=10  a = 9/10
# return (int((3*$step-3)/4) * 4 + 2)/$step + 1;
# is full circle less 4,3,2,1 as step-2 mod 4
#
# sub dir4_maximum {
#   my ($self) = @_;
#   if ($self->{'step'} == 0) {
#     return 0;   # horizontal only
#   }
#   my $step = $self->{'step'};
#   if ($self->{'ring_shape'} eq 'polygon') {
#     return (($step-2)%4 - 4)/$step + 4;
#   }
#   return 4; # supremum, full circle
# }

# want a >= 1
# 1/4 + (k+1/2)/step >= 1
# (k+1/2)/step >= 3/4
# k+1/2 >= 3*step/4
# k >= 3*step/4 - 1/2
# k >= (3*step-2)/4
# k = ceil((3*step-2)/4)
#   = floor((3*step-2)/4 + 3/4)
#   = floor((3*step+1)/4)
# min_a = 1/4 + (floor((3*step+1)/4) + 1/2)/step - 1
#       = (1 + 4*(floor((3*step+1)/4) + 1/2)/step ) / 4
#       = ((4*floor((3*step+1)/4) + 2)/step + 1) / 4 - 1
#       = ((floor((3*step+1)/4)*4 + 2)/step - 3) / 4
# return (int((3*$step+1)/4) * 4 + 2)/$step - 3;
# is 0,1,2,3 as step-2 mod 4
# return (($step-2) % 4) / $step;
#
# but last of ring across to first of next may be shallower
#
# sub dir4_minimum {
#   my ($self) = @_;
#   my $step = $self->{'step'};
#   if ($self->{'ring_shape'} eq 'polygon') {
#     if ($step % 4 != 2) {   # polygon step=2mod4 includes horizontal ...
#       my ($dx,$dy) = $self->n_to_dxdy($self->{'step'});
#       return min (atan2($dy,$dx) * (2/_PI),
#                   (($step-2) % 4) / $step);
#     }
#
#   }
#   return 0; # horizontal
# }

sub dir_minimum_dxdy {
  my ($self) = @_;
  my $step = $self->{'step'};
  if ($self->{'ring_shape'} eq 'polygon') {
    return $self->n_to_dxdy($step == 9
                            ? 9
                            : int((3*$step+5)/4));
  }
  return (1,0); # horizontal
}
sub dir_maximum_dxdy {
  my ($self) = @_;
  if ($self->{'step'} == 0) {
    return (1,0);   # step=0 horizontal always
  }

  if ($self->{'ring_shape'} eq 'polygon') {
    my $step = $self->{'step'};
    return $self->n_to_dxdy(int((3*$step+1)/4));  # 1 before the minimum

    # # just before 3/4 way around, then half back ....
    # # sides   side
    # # -----   ----
    # #   3      1
    # #   4      2
    # #   5      3
    # #   6      3
    # #   7      4
    # #   8      5
    # #   9      6
    # #  10      6
    # return _circlefrac_to_xy (1, int((3*$step-3)/4), $step, _PI);
  }

  return (0,0); # supremum, full circle
}

#------------------------------------------------------------------------------

sub new {
  ### MultipleRings new() ...
  my $self = shift->SUPER::new(@_);

  my $step = $self->{'step'};
  $step = $self->{'step'} = (! defined $step ? 6  # default
                             : $step < 0     ? 0  # minimum
                             : $step);
  ### $step

  my $ring_shape = ($self->{'ring_shape'} ||= 'circle');
  if (! ($ring_shape eq 'circle' || $ring_shape eq 'polygon')) {
    croak "Unrecognised ring_shape option: ", $ring_shape;
  }
  if ($step < 3) {
    # polygon shape only for step >= 3
    $ring_shape = $self->{'ring_shape'} = 'circle';
  }

  if ($ring_shape eq 'polygon') {
    ### polygon ...
    if ($step == 6) {
      ### 0.5/sin(PI/6)=1 exactly ...
      $self->{'base_r'} = 1;
    } elsif ($step == 3) {
      ### 0.5/sin(PI/3)=sqrt(3)/3 ...
      $self->{'base_r'} = sqrt(3)/3;
    } else {
      $self->{'base_r'} = 0.5/sin(_PI/$step);
    }

  } elsif ($step == 6) {
    ### 0.5/sin(PI/6) = 1 exactly ...
    $self->{'base_r'} = 0;

  } elsif ($step == 4) {
    ### 0.5/sin(PI/4) = sqrt(2)/2 ...
    $self->{'base_r'} = sqrt(2)/2 - 1;

  } elsif ($step == 3) {
    ### 0.5/sin(PI/3) = sqrt(3)/3 ...
    $self->{'base_r'} = sqrt(3)/3 - 1;

  } elsif ($step < 6) {
    ### sin: $step>1 && sin(_PI/$step)
    $self->{'base_r'} = ($step > 1 && 0.5/sin(_PI/$step)) - 1;
  }
  ### base r: $self->{'base_r'}

  return $self;
}

# with N decremented
# d = [ 1, 2, 3, 4,  5 ]
# N = [ 0, 1, 3, 6, 10 ]
#
# N = (1/2 d^2 - 1/2 d)
#   = (1/2*$d**2 - 1/2*$d)
#   = ((0.5*$d - 0.5)*$d)
#   = 0.5*$d*($d-1)
#
# d = 1/2 + sqrt(2 * $n + 1/4)
#   = 0.5 + sqrt(2*$n + 0.25)
#   = [ 1 + 2*sqrt(2n + 1/4) ] / 2
#   = [ 1 + sqrt(8n + 1) ] / 2
#
# (d+1)d/2 - d(d-1)/2
#     = [ (d^2 + d) - (d^2-d) ] / 2
#     = [ d^2 + d - d^2 + d ] / 2
#     = 2d/2 = d
#
# radius
#    step > 6     1 / (2 * sin(pi / ($d*$step))
#    step <= 6    Rbase + d
#
# usual polygon formula R = a / 2*sin(pi/n)
# cf inner radius  r = a / 2*tan(pi/n)
# along chord
#
# polygon horizontal when a=1
#   1/4 + (k+1/2)/step = 1
#   (k+1/2)/step = 3/4
#   k+1/2 = 3*step/4
#   k = 3*step/4 - 1/2
#   k = ()/4
#   4*k = 3*step-2
# and when a=1/2
#   1/4 + (k+1/2)/step = 1/2
#   (k+1/2)/step = 1/4
#   k+1/2 = step/4
#   4*k+2 = step

# 1/2 / R = sin(2pi/sides)
# 1/2 / (R^2 - 1/4) = tan(2pi/sides)
# f(x) = 1/2 / R - sin(2pi/sides)     = $f
# f'(x) = -1/2 / R^2 - cos(2pi/sides) = $slope
# $r-$f/$slope better approx
# (1/2 / R - sin(2pi/sides)) / (-1/2 / R^2 - cos(2pi/sides))
#   = (R/2 - R^2 sin(2pi/sides)) / (-1/2 - R^2 * cos(2pi/sides))

sub n_to_xy {
  my ($self, $n) = @_;
  ### MultipleRings n_to_xy(): "n=$n  step=$self->{'step'} shape=$self->{'ring_shape'}"

  # "$n<1" separate test from decrement so as to warn on undef
  # don't have anything sensible for infinity, and _PI / infinity would
  # throw a div by zero
  if ($n < 1) { return; }
  if (is_infinite($n)) { return ($n,$n); }
  $n -= 1;

  ### decremented n: $n
  my $step = $self->{'step'};
  if (! $step) {
    ### step==0 goes along X axis ...
    return ($n, 0);
  }

  my $d = int((sqrt(int(8*$n/$step) + 1) + 1) / 2);

  ### d frac: (sqrt(int(8*$n) + 1) + 1) / 2
  ### d int: "$d"
  ### base: ($d*($d-1)/2).''
  ### next base: (($d+1)*$d/2).''
  ### assert: $n >= ($d*($d-1)/2)
  ### assert: $n < ($step * ($d+1) * $d / 2)

  $n -= $d*($d-1)/2 * $step;
  ### n remainder: "$n"
  ### assert: $n >= 0
  ### assert: $n < $d*$step

  my $zero = $n * 0;
  if (ref $n) {
    if ($n->isa('Math::BigInt')) {
      $n = Math::PlanePath::SacksSpiral::_bigfloat()->new($n);
    } elsif ($n->isa('Math::BigRat')) {
      $n = $n->as_float;
    }
    if ($n->isa('Math::BigFloat')) {
      ### bigfloat ...
      $d = Math::BigFloat->new($d);
    }
  }
  my $pi = _pi($n);
  ### $pi

  # my $base_r = $self->{'base_r'};
  #     $base_r = Math::BigFloat->new($base_r);

  {
    my $numsides;
    my $r;
    if ($self->{'ring_shape'} eq 'circle')  {
      ### circle ...
      $numsides = $d * $step;
      if ($step > 6) {
        $r = 0.5 / sin($pi / $numsides);
      } else {
        my $base_r;
        if ($step == 6) {
          $base_r = 0; # exactly
        } elsif ($step == 4) {
          ### 0.5/sin(PI/4)=sqrt(2)/2 ...
          $base_r = sqrt(0.5 + $zero) - 1;  # sqrt() instead of sin()
        } elsif ($step == 3) {
          ### 0.5/sin(PI/3)=sqrt(3)/3 ...
          $base_r = sqrt(3 + $zero)/3 - 1;  # sqrt() instead of sin()
        } elsif ($step == 1) {
          $base_r = -1;             # so initial d=1 at $r=0
        } else {
          $base_r = 0.5/sin($pi/$step) - 1;
        }
        $r = $base_r + $d;
      }
    } else {
      ### polygon ...
      $numsides = $step;
      my $base_r = _numsides_to_r($step,$pi);
      if ($step > 6) {
        $r = $base_r*$d;
      } else {
        $r = $base_r + ($d-1)/cos($pi/$step);
      }
      $n /= $d;
    }
    ### n with frac: $n

    # numsides even N > numsides/2
    # numsides odd  N >= (numsides+1)/2 = ceil(numsides/2)
    my $y_neg;
    if (2*$n >= $numsides) {
      $n = $numsides - $n;
      $y_neg = 1;
    }

    my $x_neg;
    my $xy_transpose;
    if ($numsides % 2 == 0) {
      if (4*$n >= $numsides) {
        $n = $numsides/2 - $n;
        $x_neg = 1;
      }
      if ($numsides % 4 == 0 && 8*$n >= $numsides) {
        $n = $numsides/4 - $n;
        $xy_transpose = 1;
      }
    }

    my $side = int ($n);
    $n -= $side;

    my ($x, $y) = _circlefrac_to_xy($r, $side, $numsides, $pi);

    if ($n) {
      # fractional $n offset into side

      my ($to_x, $to_y);
      $side += 1;
      if (2*$side == $numsides+1) {
        # vertical at left, so X unchanged Y negate
        $to_x = $x;
        $to_y = - $y;

      } elsif (4*$side == $numsides+2 || 4*$side == 3*$numsides-2) {
        # horizontal at top or bottom, so Y unchanged X negate
        $to_x = - $x;
        $to_y = $y;

      } else {
        ($to_x, $to_y) = _circlefrac_to_xy($r, $side, $numsides, $pi);
      }

      ### $side
      ### $r
      ### from: "$x, $y"
      ### to: "$to_x, $to_y"

      # If vertical or horizontal then don't apply the proportions since the
      # two parts $x*$n and $to_x*(1-$n) can round off giving the sum != to
      # the original $x.
      if ($to_x != $x) {
        $x = $x*(1-$n) + $to_x*$n;
      }
      if ($to_y != $y) {
        $y = $y*(1-$n) + $to_y*$n;
      }
    }

    if ($xy_transpose) {
      ($x,$y) = ($y,$x);
    }
    if ($x_neg) {
      $x = -$x;
    }
    if ($y_neg) {
      $y = -$y;
    }

    ### final: "x=$x y=$y"
    return ($x, $y);
  }

  # {
  #   # && $d != 0 # watch out for overflow making d==0 ??
  #   #
  #   my $d_step = $d*$step;
  #   my $r = ($step > 6
  #            ? 0.5 / sin($pi / $d_step)
  #            : $base_r + $d);
  #   ### r: "$r"
  #
  #   my $n2 = 2*$n;
  #
  #   if ($n2 == int($n2)) {
  #     if (($n2 % $d_step) == 0) {
  #       ### theta=0 or theta=pi, exactly on X axis ...
  #       return ($n ? -$r : $r,  # n remainder 0 means +ve X axis, non-zero -ve
  #               0);
  #     }
  #     if (($d_step % 2) == 0) {
  #       my $n2sub = $n2 - $d_step/2;
  #       if (($n2sub % $d_step) == 0) {
  #         ### theta=pi/2 or theta=3pi/2, exactly on Y axis ...
  #         return (0,
  #                 $n2sub ? -$r : $r);
  #       }
  #     }
  #   }
  #
  #   my $theta = $n2 * $pi / $d_step;
  #
  #   ### theta frac: (($n - $d*($d-1)/2)/$d).''
  #   ### theta: "$theta"
  #
  #   return ($r * cos($theta),
  #           $r * sin($theta));
  # }
}

# $side is 0 to $numsides-1
sub _circlefrac_to_xy {
  my ($r, $side, $numsides, $pi) = @_;
  ### _circlefrac_to_xy(): "r=$r side=$side numsides=$numsides pi=$pi"

  if (2*$side == $numsides) {
    ### 180-degrees, so X=R, Y=0 ...
    return (-$r, 0);

  }
  if (4*$side == $numsides) {
    ### 90-degrees, so X=0, Y=R ...
    return (0, $r);
  }
  if (6*$side == $numsides) {
    ### 60-degrees, so X=R/2, Y=sqrt(3)/2*R ...
    return ($r / 2,
            $r * sqrt(3 + $r*0) / 2);
  }
  if (8*$side == $numsides) {
    ### 45-degrees, so X=Y=R/sqrt(2) ...
    my $x = $r / sqrt(2 + $r*0);
    return ($x, $x);
  }

  # my $two_pi = (ref $r && $r->isa('Math::BigFloat')
  #               ? 2*Math::BigFloat->bpi;
  #               : 2*_PI);
  #
  # if (2*$side == $numsides+1) {
  #   ### first below X axis ...
  #   my $theta = 2*$pi * ($side-1)/$numsides;
  #   return ($r * cos($theta),
  #           - $r * sin($theta));
  # }
  # if (4*$side == $numsides+1) {
  #   ### first past Y axis ...
  #   my $theta = 2*$pi * ($side-1)/$numsides;
  #   return (- $r * cos($theta),
  #           $r * sin($theta));
  # }

  my $theta = 2 * $pi * $side/$numsides;
  return ($r * cos($theta),
          $r * sin($theta));
}

# my $numsides = $step;
# if ($self->{'ring_shape'} eq 'polygon') {
#   $n /= $d;
#   my $base_r = _numsides_to_r($step,$pi);
#   if ($step > 6) {
#     $r = $base_r*$d;
#   } else {
#     $r = $base_r + ($d-1)/cos($pi/$step);
#   }
# } else {
#   $numsides *= $d;
#   if ($step > 6) {
#     $r = _numsides_to_r($numsides,$pi);
#   } else {
#     $r = _numsides_to_r($step,$pi) + $d;
#   }
# }
# my $side = int($n);
# $n -= $side;

sub _numsides_to_r {
  my ($numsides, $pi) = @_;
  if ($numsides == 3) { return sqrt(0.75 + $pi*0); }
  if ($numsides == 4) { return sqrt(0.5 + $pi*0); }
  if ($numsides == 6) { return 1 + $pi*0; }
  return 0.5 / sin($pi/$numsides);
}


# for step=4
#   R   = sqrt(2)/2 + d
#   R^2 = (sqrt(2)/2 + d)^2
#       = 2/4 + 2*sqrt(2)/2*d + d^2
#       = 1/2 + d*sqrt(2) + d^2
#   not an integer
#
sub n_to_rsquared {
  my ($self, $n) = @_;
  ### MultipleRings n_to_rsquared(): "n=$n"
  if ($n < 1) { return undef; }
  if (is_infinite($n)) { return $n; }

  if (defined (my $r = _n_to_radius_exact($self,$n))) {
    return $r*$r;
  }
  if ($self->{'step'} == 1) {
    # $n < 4 covered by _n_to_radius_exact()

    if ($n >= 4 && $n < 7) {
      # triangle numsides=3
      #   N=4 at X=2, Y=0
      #   N=5 at X=-1, Y=sqrt(3)
      #   N=4+f at X=2-3*f Y=f*sqrt(3)
      #     R^2 = (2-3f)^2 + 3*f^2
      #         = 4-12f+9*f^2 + 3*f^2
      #         = 4-12f+12*f^2
      #         = 4*(1 - 3f + 3*f^2)
      #         = 4 - 6*(2*f) + 3*(2*f)^2
      #     f=1/2 is R^2 = 1
      #   N=5+f at X=-1 Y = sqrt(3)*(1-2*f)
      #     R^2 = 1 + 3*(1-2*f)^2
      #         = 1 + 3 - 3*4*f + 3*4*f^2
      #         = 4 - 12*f + 12*f^2
      #         = 4 - 12*(f - f^2)
      #         = 4 - 12*f*(1 - f)

      $n -= int($n);
      return 4 - 12*$n*(1-$n);
    }

    if ($n >= 7 && $n < 11) {
      ### square numsides=4 ...
      # X=3-3*f Y=3*f
      # R^2 = (3-3*f)^2 + (3*f)^2
      #     = 9*[ (1-f)^2 + f^2) ]
      #     = 9*[ 1 - 2f + f^2 + f^2) ]
      #     = 9*[ 1 - 2f + 2f^2 ]
      #     = 9*[ 1 - 2(f - f^2) ]
      #     = 9 - 18*f*(1 - f)
      # eg f=1/2 R^2 = (sqrt(2)/2*3)^2 = 2/4*9 = 9/2

      $n -= int($n);
      return 9 - 18*$n*(1-$n);
    }

    if ($n >= 16 && $n < 22) {
      ### hexagon numsides=6 ...
      # X=5 Y=0  to X=5*1/2 Y=5*sqrt(3)/2
      # R^2 = (5 - 5/2*f)^2 + (5*sqrt(3)/2*f)^2
      #     = 25 - 25*f + 25*f^2
      #     = 25 - 25*f*(1-f)
      # eg f=1/2 R^2 = 18.75
      # or f=1/5 R^2 = 21 exactly, though 1/5 not exact in binary floats

      $n -= int($n);
      return 25 - 25*$n*(1-$n);
    }

    # other numsides don't have sin(pi/numsides) an integer or sqrt so
    # aren't an exact R^2
  }

  # ENHANCE-ME: step=1 various exact values for ring of 4 and ring of 6

  return $self->SUPER::n_to_rsquared($n);
}
sub n_to_radius {
  my ($self, $n) = @_;
  ### n_to_radius(): $n

  if ($n < 1) { return undef; }
  if (is_infinite($n)) { return $n; }

  if (defined (my $r = _n_to_radius_exact($self,$n))) {
    return $r;
  }
  return sqrt($self->n_to_rsquared($n));
  # return $self->SUPER::n_to_radius($n);
}

# step=6 shape=polygon exact integer for some of second ring too
# sub n_to_trsquared {
#   my ($self, $n) = @_;
#   ### MultipleRings n_to_rsquared(): "n=$n"
# }

sub _n_to_radius_exact {
  my ($self, $n) = @_;
  ### _n_to_radius_exact(): "n=$n step=$self->{'step'}"

  if ($n < 1) { return undef; }
  if (is_infinite($n)) { return $n; }

  my $step = $self->{'step'};
  if ($step == 0) {
    return $n - 1;    # step=0 goes along X axis starting X=0,Y=0
  }

  if ($step == 1) {
    if ($n < 4) {
      if ($n < 2) {
        return 0;  # 0,0 only, no jump across to next ring
      }
      $n -= int($n);
      return abs(1-2*$n);
    }
    if ($n == int($n)) {
      ### step=1 radius=integer steps for integer N ...
      return _n0_to_d($self,$n-1) - 1;
    }
    my $two_n = 2*$n;
    if ($two_n == 9 || $two_n == 11 || $two_n == 13) {
      #  N=4.5 at X=1/2 Y=sqrt(3)/2  R^2 = 1/4 + 3/4 = 1 exactly
      #  N=5.5 at X=-1, Y=0 so R^2 = 1 exactly
      #  N=6.5 same as N=4.5
      return 1;
    }

  } elsif ($step == 6) {
    if ($n == int($n)) {
      # step=6 circle all integer N has exact integer radius
      # step=6 polygon only innermost ring N<=6 exact integer radius
      if ($self->{'ring_shape'} eq 'circle'
          || $n <= 6) {   # ring_shape=polygon
        return _n0_to_d($self,$n-1);
      }
    }
  }

  ### no exact radius ...
  return undef;
}
sub _n0_to_d {
  my ($self, $n) = @_;
  return int((sqrt(int(8*$n/$self->{'step'}) + 1) + 1) / 2);
}
sub _d_to_n0base {
  my ($self, $d) = @_;
  return $d*($d-1)/2 * $self->{'step'};
}

# From above
#     r = 0.5 / sin(pi/(d*step))
#
#     sin(pi/(d*step)) = 0.5/r
#     pi/(d*step) = asin(1/(2*r))
#     1/d * pi/step = asin(1/(2*r))
#     d = pi/(step*asin(1/(2*r)))
#
# r1 = 0.5 / sin(pi/(d*step))
# r2 = 0.5 / sin(pi/((d+1)*step))
# r2 - r1 = 0.5 / sin(pi/(d*step)) - 0.5 / sin(pi/((d+1)*step))
# r2-r1 >= 1 when step>=7 ?

sub _xy_to_d {
  my ($self, $x, $y) = @_;
  ### _xy_to_d(): "x=$x y=$y"

  my $r = hypot ($x, $y);
  if ($r < 0.5) {
    ### r smaller than 0.5 ring, treat as d=1
    # 1/(2*r) could be div-by-zero
    # or 1/(2*r) > 1 would be asin()==-nan
    return 1;
  }
  my $two_r = 2*$r;
  if (is_infinite($two_r)) {
    ### 1/inf is a divide by zero, avoid that ...
    return $two_r;
  }
  ### $r

  my $step = $self->{'step'};
  if ($self->{'ring_shape'} eq 'polygon') {
    my $theta_frac = _xy_to_angle_frac($x,$y);
    $theta_frac -= int($theta_frac*$step) / $step;  # modulo 1/step

    my $r = hypot ($x, $y);
    my $alpha = 2*_PI/$step;
    my $theta = 2*_PI * $theta_frac;
    ### $r
    ### x=r*cos(theta): $r*cos($theta)
    ### y=r*sin(theta): $r*sin($theta)

    my $p = $r*cos($theta) + $r*sin($theta) * sin($alpha/2)/cos($alpha/2);
    ### $p
    ### base_r: $self->{'base_r'}
    ### p - base_r: $p - $self->{'base_r'}

    if ($step >= 6) {
      return $p / $self->{'base_r'};
    } else {
      return ($p - $self->{'base_r'}) * cos(_PI/$step) + 1;
    }
  }

  if ($step > 6) {
    ### d frac by asin: _PI / ($step * asin(1/$two_r))
    return _PI / ($step * asin(1/$two_r));
  } else {
    # $step <= 6
    ### d frac by base: $r - $self->{'base_r'}
    return $r - $self->{'base_r'};
  }
}

sub xy_to_n {
  my ($self, $x, $y) = @_;
  ### MultipleRings xy_to_n(): "$x, $y  step=$self->{'step'}  shape=$self->{'ring_shape'}"

  my $n;
  my $step = $self->{'step'};
  if ($step == 0) {
    # step==0
    $n = int ($x + 1.5);

  } else {
    my $theta_frac = _xy_to_angle_frac($x,$y);
    ### $theta_frac
    ### assert: (0 <= $theta_frac && $theta_frac < 1)  || $theta_frac!=$theta_frac

    my $d;
    if ($self->{'ring_shape'} eq 'polygon') {
      $n = int($theta_frac*$step);
      $theta_frac -= $n/$step;
      ### theta modulo 1/step: $theta_frac
      ### $n

      my $r = hypot ($x, $y);
      my $alpha = 2*_PI/$step;
      my $theta = 2*_PI * $theta_frac;
      ### $r
      ### so x=r*cos(theta): $r*cos($theta)
      ### so y=r*sin(theta): $r*sin($theta)

      my $pi = _PI;
      my $p = $r*cos($theta) + $r*sin($theta) * sin($alpha/2)/cos($alpha/2);
      my $base_r = Math::PlanePath::MultipleRings::_numsides_to_r($step,$pi);
      ### $p
      ### $base_r

      if ($step > 6) {
        $d = $p / $base_r;
      } else {
        $d = ($p - $base_r) * cos($pi/$step) + 1;
      }
      ### d frac: $d
      $d = int($d+0.5);
      ### $d
      ### cf _xy_to_d(): _xy_to_d($self,$x,$y)

      my $f = ($p == 0 ? 0 : $r*sin($theta) / ($p*sin($alpha)));
      $n = int(($n+$f)*$d + 0.5);

      ### e: $r*sin($theta) * sin($alpha/2)/cos($alpha/2)
      ### $f
      ### $n

    } else {
      $d = int(_xy_to_d($self,$x,$y) + 0.5);
      ### $d
      $n = int (0.5 + $theta_frac * $d*$step);
      if ($n >= $d*$step) { $n = 0; }
    }

    ### n within ring: $n
    ### n ring start: _d_to_n0base($self,$d) + 1

    $n += _d_to_n0base($self,$d) + 1;
    ### $d
    ### d base: 0.5*$d*($d-1)
    ### d base M: $step * 0.5*$d*($d-1)
    ### $theta_frac
    ### theta offset: $theta_frac*$d
    ### $n
  }

  ### trial n: $n
  if (my ($nx, $ny) = $self->n_to_xy($n)) {
    ### nxy: "nx=$nx ny=$ny  hypot=".hypot($x-$nx,$y-$ny)
    ### cf orig xy: "x=$x y=$y"
    if (hypot($x-$nx, $y-$ny) <= 0.5) {
      return $n;
    }
  }
  return undef;
}

# ENHANCE-ME: step>=3 small rectangles around 0,0 don't cover any pixels
#
# not exact
sub rect_to_n_range {
  my ($self, $x1,$y1, $x2,$y2) = @_;
  ### MultipleRings rect_to_n_range(): "$x1,$y1, $x2,$y2  step=$self->{'step'}"

  my $zero = ($x1<0) != ($x2<0) || ($y1<0) != ($y2<0);
  my $step = $self->{'step'};

  my ($r_lo, $r_hi) = Math::PlanePath::SacksSpiral::_rect_to_radius_range
    ($x1,$y1, $x2,$y2);
  ### $r_lo
  ### $r_hi
  if (is_infinite($r_hi)) {
    return (1,$r_hi);
  }
  if ($r_hi < 1) { $r_hi = 1; }
  if ($self->{'ring_shape'} eq 'polygon') {
    $r_hi /= cos(_PI/$self->{'step'});
    ### poly increase r_hi: $r_hi
  }

  my ($d_lo, $d_hi);
  if ($self->{'ring_shape'} eq 'polygon') {
    if ($step >= 6) {
      $d_lo = $r_lo / $self->{'base_r'};
      $d_hi = $r_hi / $self->{'base_r'};
    } else {
      $d_lo = ($r_lo - $self->{'base_r'}) * cos(_PI/$step) + 1;
      $d_hi = ($r_hi - $self->{'base_r'}) * cos(_PI/$step) + 1;
    }
  } else {
    if ($step > 6) {
      $d_lo = ($r_lo > 0
               ? _PI / ($step * asin(0.5/$r_lo))
               : 0);
      $d_hi = _PI / ($step * asin(0.5/$r_hi));
    } else {
      $d_lo = $r_lo - $self->{'base_r'};
      $d_hi = $r_hi - $self->{'base_r'};
    }
  }
  ### $d_lo
  ### $d_hi

  $d_lo = int($d_lo - 1);
  $d_hi = int($d_hi + 2);
  if ($d_lo < 1) { $d_lo = 1; }

  if ($step) {
    # start of ring is N= 0.5*$d*($d-1) * $step + 1
    ### n_lo: 0.5*$d_lo*($d_lo-1) * $step + 1
    ### n_hi: 0.5*$d_hi*($d_hi+1) * $step
    return ($d_lo*($d_lo-1)/2 * $step + 1,
            $d_hi*($d_hi+1)/2 * $step);
  } else {
    # $step == 0
    return ($d_lo, $d_hi);
  }





  # # if x1,x2 pos and neg then 0 is covered and it's the minimum
  # # ENHANCE-ME: might be able to be a little tighter on $d_lo
  # my $d_lo = ($zero
  #             ? 1
  #             : max (1, -2 + int (_xy_to_d ($self,
  #                                           min($x1,$x2),
  #                                           min($y1,$y2)))));
  # my $d_hi = 1 + int (_xy_to_d ($self,
  #                               max($x1,$x2),
  #                               max($y1,$y2)));
  # ### $d_lo
  # ### $d_hi
  # if ((my $step = $self->{'step'})) {
  #   # start of ring is N= 0.5*$d*($d-1) * $step + 1
  #   ### n_lo: 0.5*$d_lo*($d_lo-1) * $step + 1
  #   ### n_hi: 0.5*$d_hi*($d_hi+1) * $step
  #   return ($d_lo*($d_lo-1)/2 * $step + 1,
  #           $d_hi*($d_hi+1)/2 * $step);
  # } else {
  #   # $step == 0
  #   return ($d_lo, $d_hi);
  # }
}

#------------------------------------------------------------------------------
# generic

# _xy_to_angle_frac() returns the angle of X,Y as a fraction 0 <= angle < 1
# measured anti-clockwise around from the X axis.
#
sub _xy_to_angle_frac {
  my ($x, $y) = @_;

  # perlfunc.pod warns atan2(0,0) is implementation dependent.  The C99 spec
  # is atan2(+/-0, -0) returns +/-pi, both of which would come out 0.5 here.
  # Prefer 0 for any +/-0,+/-0.
  if ($x == 0 && $y == 0) {
    return 0;
  }

  my $frac = atan2($y,$x) * (0.5 / _PI);
  ### $frac
  if ($frac < 0) { $frac += 1; }
  elsif ($frac >= 1) { $frac -= 1; }
  return $frac;
}

# return pi=3.14159 etc, inheriting precision etc from $n if it's a BigFloat
# or other overload
sub _pi {
  my ($n) = @_;
  if (ref $n) {
    if ($n->isa('Math::BigFloat')) {
      my $digits;
      if (defined($digits = $n->accuracy)) {
        ### n accuracy ...
      } elsif (defined($digits = $n->precision)) {
        ### n precision ...
        $digits = -$digits + 1;
      } elsif (defined($digits = Math::BigFloat->accuracy)) {
        ### global accuracy ...
      } elsif (defined($digits = Math::BigFloat->precision)) {
        ### global precision ...
        $digits = -$digits + 1;
      } else {
        ### div_scale ...
        $digits = Math::BigFloat->div_scale+1;
      }
      ### $digits
      $digits = max (1, $digits);
      return Math::BigFloat->bpi($digits);
    }
    ### other overload n class: ref $n
    my $zero = $n * 0;
    return 2*atan2($zero,1+$zero);
  }
  return _PI;
}

1;
__END__

=for stopwords Ryde Math-PlanePath Pentagonals Nring ie OEIS spacings numsides Nrem pronic pronics RSquared

=head1 NAME

Math::PlanePath::MultipleRings -- rings of multiples

=head1 SYNOPSIS

 use Math::PlanePath::MultipleRings;
 my $path = Math::PlanePath::MultipleRings->new (step => 6);
 my ($x, $y) = $path->n_to_xy (123);

=head1 DESCRIPTION

This path puts points on concentric rings.  Each ring has "step" many points
more than the previous and the first is also "step".  For example with the
default step==6,

                24  23                    innermost ring  6
             25        22                 next ring      12
                  10                      next ring      18
          26   11     9  21  ...                    ringnum*step

        27  12   3  2   8  20  38

       28  13   4    1   7  19  37        <- Y=0

        29  14   5  6  18  36

          30   15    17  35
                  16
             31        24
                32  33

                  ^
                 X=0

X,Y positions are not integers, except on the axes.  The innermost ring like
N=1to6 above has points 1 unit apart.  Subsequent rings are a unit chord or
unit radial, whichever ensures no overlap.

      step <= 6      unit spacing radially
      step >= 6      unit chords around the rings

For step=6 the two spacings are the same.  Unit radial spacing ensures the X
axis points N=1,7,19,37,etc shown above are 1 unit apart.  Unit chord
spacing ensures adjacent points such as N=7,8,0,etc don't overlap.

The layout is similar to the various spiral paths of corresponding step.
For example step=6 is like the C<HexSpiral>, but rounded out to circles
instead of a hexagonal grid.  Similarly step=4 the C<DiamondSpiral> or
step=8 the C<SquareSpiral>.

The step parameter is also similar to the C<PyramidRows> with the rows
stretched around circles, but C<PyramidRows> starts from a 1-wide initial
row whereas for C<MultipleRings> here the first is "step" many.

=head2 X Axis

The starting Nring=1,7,19,37 etc on the X axis for the default step=6 is
S<6*d*(d-1)/2 + 1>, counting the innermost ring as d=1.  In general Nring is
a multiple of the X<Triangular numbers>triangular numbers d*(d-1)/2, plus 1,

    Nring = step*d*(d-1)/2 + 1

X<Centred polygonal numbers>This is the centred polygonal numbers, being the
cumulative count of points making concentric polygons or rings in the style
of this path.

Straight line radials further around arise from adding multiples of d, so
for example in step=6 shown above the line N=3,11,25,etc is S<Nring + 2*d>.
Multiples k*d with kE<gt>=step give lines which are in between the base ones
from the innermost ring.

=head2 Step 1

For step=1 the first ring is 1 point and each subsequent ring has 1 further
point.

=cut

# math-image --path=MultipleRings,step=1 --expression='i<29?i:0' --output=numbers --size=80x25

=pod

                24
                               23
             18       12    17
    25              8
          13     5

    19     9     3  1  2  4  7 11 16 22     <- Y=0

          14     6
    26             10
             20       15    21
                               28
                27

                    ^
     -5 -4 -3 -2-1 X=0 1  2  3  4  5  6

The rings are

    polygon        radius     N values
    ------------   ------     --------
    single point     0         1
    two points       1         2, 3
    triangle         2         4, 5, 6
    square           3         7, 8, 9,10
    pentagon         4        11,12,13,14,15
    hexagon          5        16,17,18,19,20,21
     etc

The X axis as described above is the triangular numbers plus 1,
ie. S<k*(k+1)/2 + 1>.

=head2 Step 2

For step=2 the arrangement is roughly

=cut

# math-image --path=MultipleRings,step=2 --expression='i<43?i:0' --output=numbers --size=80x25

=pod

                   34
          35                33
                24 15 23
    36 25                      22 32
             16  9  4  8 14

    37 26 17 10  5  2  1  3  7 13 21 31

             18 11  6 12 20
    38 27                      30 42
                28 19 29
          39                41
                   40

The pattern is similar to the C<SacksSpiral> (see
L<Math::PlanePath::SacksSpiral>).  In C<SacksSpiral> each spiral loop is 2
more points than the previous the same as here, but the positioning differs.
Here the X axis is the pronic numbers and the squares are to the left,
whereas in C<SacksSpiral> rotated around to squares on X axis and pronics to
the left.

=head2 Ring Shape

Option C<ring_shape =E<gt> 'polygon'> puts the points on concentric polygons
of "step" many sides, so each concentric polygon has 1 more point on each of
its sides than the previous polygon.  For example step=4 gives 4-sided
polygons, ie. diamonds,

    ring_shape=>'polygon', step=>4

                  16
                /    \
             17    7   15
           /    /     \   \
        18    8    2    6   14
      /     /   /    \    \    \
    19   9    3         1    5   13
      \     \   \    /    /    /
        20   10    4   12   24
           \    \    /    /
             21   11   23
                \    /
                  22

The polygons are scaled to keep points 1 unit apart.  For stepE<gt>=6 this
means 1 unit apart sideways.  step=6 is in fact a honeycomb grid where each
points is 1 away from all six of its neighbours.

For step=3, 4 and 5 the polygon sides are 1 apart radially, as measured in
the centre of each side.  This makes points a little more than 1 apart along
the sides.  Squeezing them up to make the closest points exactly 1 apart is
possible, but may require iterating a square root for each ring.  step=3
squeezed down would in fact become a variable spacing with successively four
close then one wider.

For step=2 and step=1 in the current code the default circle shape is used.
Should that change?  Is there a polygon style with 2 sides or 1 side?

The polygon layout is only a little different from a circle, but it lines up
points on the sides and that might help show a structure for some sets of
points plotted on the path.

=head2 Step 3 Pentagonals

For step=3 the pentagonal numbers 1,5,12,22,etc, P(k) = (3k-1)*k/2, are a
radial going up to the left, and the second pentagonal numbers 2,7,15,26,
S(k) = (3k+1)*k/2 are a radial going down to the left, respectively 1/3 and
2/3 the way around the circles.

As described in L<Math::PlanePath::PyramidRows/Step 3 Pentagonals>, those
P(k) and preceding P(k)-1, P(k)-2, and S(k) and preceding S(k)-1, S(k)-2 are
all composites, so plotting the primes on a step=3 C<MultipleRings> has two
radial gaps where there's no primes.

=head1 FUNCTIONS

See L<Math::PlanePath/FUNCTIONS> for behaviour common to all path classes.

=over 4

=item C<$path = Math::PlanePath::MultipleRings-E<gt>new (step =E<gt> $integer)>

=item C<$path = Math::PlanePath::MultipleRings-E<gt>new (step =E<gt> $integer, ring_shape =E<gt> $str)>

Create and return a new path object.

The C<step> parameter controls how many points are added in each circle.  It
defaults to 6 which is an arbitrary choice and the suggestion is to always
pass in a desired count.

=item C<($x,$y) = $path-E<gt>n_to_xy ($n)>

Return the X,Y coordinates of point number C<$n> on the path.

C<$n> can be any value C<$n E<gt>= 1> and fractions give positions on the
rings in between the integer points.  For C<$n < 1> the return is an empty
list since points begin at 1.

Fractional C<$n> currently ends up on the circle arc between the integer
points.  Would straight line chords between them be better, reflecting the
unit spacing of the points?  Neither seems particularly important.

=item C<$n = $path-E<gt>xy_to_n ($x,$y)>

Return an integer point number for coordinates C<$x,$y>.  Each integer N
is considered the centre of a circle of diameter 1 and an C<$x,$y> within
that circle returns N.

The unit spacing of the points means those circles don't overlap, but they
also don't cover the plane and if C<$x,$y> is not within one then the return
is C<undef>.

=item C<$str = $path-E<gt>figure ()>

Return "circle".

=back

=head1 FORMULAS

=head2 N to X,Y - Circle

As per above, each ring begins at

    Nring = step*d*(d-1)/2 + 1

This can be inverted to get the ring number d for a given N, and then
subtract Nring for a remainder into the ring.  (N-1)/step in the formula
effectively converts into triangular number style.

    d = floor((sqrt(8*(N-1)/step + 1) + 1) / 2)
    Nrem = N - Nring

Rings are sized so that points are spaced 1 unit apart.  There are three
cases,

    circle,  step<=6     unit radially on X axis
    polygon, step<=6     unit radially on sides centre
             step>=7     unit chord between points

For the circle shape the integer points are on a circle and fractional N is
on a straight line between those integer points.  This means it's a polygon
too, but one with ever more sides whereas ring_shape=polygon is a fixed
"step" many sides.

    circle       numsides = d*step
    polygon      numsides = step

The radial distance to a polygon corner is calculated as

                           base               varying with d
    ----------------     ---------------------------------------
    circle,  step<=6     0.5/sin(pi/step) + d-1
    polygon, step<=6     0.5/sin(pi/step) + (d-1)/cos(pi/step)
    circle,  step>=7     0                + 0.5/sin(pi/(d*step))
    polygon, step>=7     0                + d * 0.5/sin(pi/step)

The stepE<lt>=6 cases are an initial polygon of "step" many unit sides, then
unit spacing d-1 for circle, or for polygon (d-1)/cos(pi/step) which is
bigger and ensures the middle of the sides have unit spacing radially.

The 0.5/sin(pi/step) for radius of a unit sided polygon arises from

          r      ___---*
           ___---      | 1/2 = half the polygon side
     ___--- alpha      |
    o------------------+

    alpha = (2pi/numsides) / 2 = pi/numsides
    sin(alpha) = (1/2) / base_r
    r = 0.5 / sin(pi/numsides)

The angle theta to a polygon vertex is simply a full circle divided by
numsides.

    side = circle   Nrem
           polygon  floor(Nrem / step)
    theta = side * (2pi / numsides)
    vertex X = r * cos(theta)
           Y = r * sin(theta)

    next_theta = (side+1) * (2pi / numsides)
    next_vertex X = r * cos(next_theta)
                Y = r * sin(next_theta)

    frac into side
    f = circle   frac(Nrem)    = Nrem modulo 1
        polygon  Nrem - side*d = Nrem modulo d

    X = vertex_X + f * (next_vertex_X - vertex_X)
    Y = vertex_Y + f * (next_vertex_Y - vertex_Y)

If Nrem is an integer for circle, or multiple of d for polygon, then the
vertex X,Y is the final X,Y, otherwise a fractional distance between the
vertex X,Y and next vertex X,Y.

For a few cases X or Y are exact integers.  Special case code for these
cases can ensure floating point rounding of pi doesn't give small offsets
from integers.

For step=6 the base r is r=1 exactly since the innermost ring is a little
hexagon.  This means for the circle step=6 case the points on the X axis
(positive and negative) are all integers X=1,2,3,etc.

       P-----P
      /   1 / \ 1  <-- innermost points 1 apart
     /     /   \
    P     o-----P   <--  base_r = 1
     \      1  /
      \       /
       P-----P

If theta=pi, which is when 2*Nrem==d*step, then the point is on the negative
X axis.  Returning Y=0 exactly for that avoids sin(pi) giving some small
non-zero due to rounding.

If theta=pi/2 or theta=3pi/2, which is 4*Nrem==d*step or 4*Nrem==3*d*step,
then N is on the positive or negative Y axis (respectively).  Returning X=0
exactly avoids cos(pi/2) or cos(3pi/2) giving some small non-zero.

Points on the negative X axis points occur when the step is even.  Points on
the Y axis points occur when the step is a multiple of 4.

If theta=pi/4, 3*pi/4, 5*pi/4 or 7*pi/4, which is 8*Nrem==d*step, 3*d*step,
5*d*step or 7*d*step then the points are on the 45-degree lines X=Y or X=-Y.
The current code doesn't try to ensure X==Y in these cases.  The values are
not integers and floating point rounding might mean sin(pi/4)!=cos(pi/4)
resulting in X!=Y.

=head2 N to RSquared - Step 1

For step=1 the rings are point, line, triangle, square, pentagon, etc, with
vertices at radius=numsides-1.  For fractional N the triangle, square and
hexagon cases are quadratics in the fraction part, allowing exact values
from C<n_to_rsquared()>.

           Ring                    R^2
    ---------------------     --------------
    triangle   4 <= N < 7      4 - 12*f*(1-f)
    square     7 <= N < 11     9 - 18*f*(1-f)
    hexagon   16 <= N < 22    25 - 25*f*(1-f)

    f = N - int(N)  fractional part of N

For example for the square at N=7.5 have f=0.5 and R^2=4.5 exactly.  These
quadratics arise because sine of 2pi/3, 2pi/4 and 2pi/6 are square roots,
which on squaring up in R^2=X^2+Y^2 become integer factors for the fraction
f along the polygon side.

=head1 OEIS

Entries in Sloane's Online Encyclopedia of Integer Sequences related to
this path include

=over

L<http://oeis.org/A005448> (etc)

=back

    A005448 A001844 A005891 A003215 A069099     3 to 7
    A016754 A060544 A062786 A069125 A003154     8 to 12
    A069126 A069127 A069128 A069129 A069130    13 to 17
    A069131 A069132 A069133                    18 to 20
        N on X axis of step=k, being the centred pentagonals

    step=1
      A002024    Radius+1, runs of n repeated n times

    step=8
      A090915    permutation N at X,-Y, mirror across X axis

=head1 SEE ALSO

L<Math::PlanePath>,
L<Math::PlanePath::SacksSpiral>,
L<Math::PlanePath::TheodorusSpiral>,
L<Math::PlanePath::PixelRings>

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
