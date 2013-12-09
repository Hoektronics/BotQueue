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


# Maybe:
#
# $bool = $path->rect_to_n_range_is_always_exact()
# $bool = $path->is_tree()
# $bool = $path->tree_n_to_subheight_is_infinite()
#    identifying the infinite spines only
#
# tree_n_ordered_children() $n and undefs
#   SierpinskiTree,ToothpickTree left and right
#   OneOfEight 3 from horiz, 5 from diag
#
# gcdxy_minimum
# gcdxy_maximum
# productxy_minimum
# trsquared_minimum
# trsquared_minimum
#
# $path->xy_integer() if X,Y both all integer
# $path->x_integer()  if X all integer
# $path->y_integer()  if Y all integer
# $path->xy_integer_n_start
#
# xy_all_coprime() xy_coprime()   gcd(X,Y)=1 always
# xy_all_divisible()   X divisible by Y
# xy_any_even
# xy_any_odd
# xy_all_even
# xy_all_odd
# xy_parity_minimum() X+Y mod 2
# xy_parity_maximum() X+Y mod 2
# xy_parity "even" "odd" "both"
# xy_hexlattice_type "centred" "side_horiz"
# xy_triangular_lattice "", "even", "odd
#
# lattice_type square,triangular,triangular_odd,pentagonal,fractional
# $path->xy_any_odd()   xy_odd()   xy_all_odd()
# $path->xy_any_even()  xy_even()  xy_all_even()
#
# $path->turn_any_left
# $path->turn_any_right
# $path->turn_any_straight
# $path->n_to_turn_lsr
# $path->n_to_dir4
# $path->n_to_turn4
# $path->n_to_turn6
# $path->n_to_turn8
# $path->n_to_ddist
# $path->n_to_drsquared
# $path->xy_to_dir4_list
# $path->xy_to_dxdy_list
# $path->xy_to_n_list_maxcount
# $path->xy_to_n_list_maxnum
# $path->xy_to_n_list_maximum
# $path->xy_next_in_rect($x,$y, $x1,$y1,$x2,$y2)
#    return ($x,$y) or empty
#
# xy_unique_n_start
# figures_disjoint
# figures_disjoint_n_start
#         separate
#         unoverlapped
#
# Math::PlanePath::Base::Generic
#   divrem
#   divrem_mutate
#


#------------------------------------------------------------------------------
package Math::PlanePath;
use 5.004;
use strict;

use vars '$VERSION';
$VERSION = 111;

# uncomment this to run the ### lines
# use Smart::Comments;


# defaults
use constant figure => 'square';
use constant default_n_start => 1;
sub n_start {
  my ($self) = @_;
  if (ref $self && defined $self->{'n_start'}) {
    return $self->{'n_start'};
  } else {
    return $self->default_n_start;
  }
}
sub arms_count {
  my ($self) = @_;
  return $self->{'arms'} || 1;
}

use constant class_x_negative => 1;
use constant class_y_negative => 1;
sub x_negative { $_[0]->class_x_negative }
sub y_negative { $_[0]->class_y_negative }
use constant n_frac_discontinuity => undef;

use constant parameter_info_array => [];
sub parameter_info_list {
  return @{$_[0]->parameter_info_array};
}

# x_negative(),y_negative() existed before x_minimum(),y_minimum(), so
# default x_minimum(),y_minimum() from those.
sub x_minimum {
  my ($self) = @_;
  return ($self->x_negative ? undef : 0);
}
sub y_minimum {
  my ($self) = @_;
  return ($self->y_negative ? undef : 0);
}
use constant x_maximum => undef;
use constant y_maximum => undef;

sub sumxy_minimum {
  my ($self) = @_;
  ### PlanePath sumxy_minimum() ...
  if (defined (my $x_minimum = $self->x_minimum)
      && defined (my $y_minimum = $self->y_minimum)) {
    ### $x_minimum
    ### $y_minimum
    return $x_minimum + $y_minimum;
  }
  return undef;
}
use constant sumxy_maximum => undef;

sub sumabsxy_minimum {
  my ($self) = @_;
  my $x_minimum = $self->x_minimum;
  my $y_minimum = $self->y_minimum;
  if (defined $x_minimum && $x_minimum >= 0
      && defined $y_minimum && $y_minimum >= 0) {
    # X>=0 and Y>=0 so abs(X)+abs(Y) == X+Y
    return $self->sumxy_minimum;
  }
  return _max($x_minimum||0,0) + _max($y_minimum||0,0);
}
use constant sumabsxy_maximum => undef;

use constant diffxy_minimum => undef;
#
# If the path is confined to the fourth quadrant, so X>=something and
# Y<=something then a minimum X-Y exists.  But fourth-quadrant-only path is
# unusual, so don't bother with code checking that.
# sub diffxy_minimum {
#   my ($self) = @_;
#   if (defined (my $y_maximum = $self->y_maximum)
#       && defined (my $x_minimum = $self->x_minimum)) {
#     return $x_minimum - $y_maximum;
#   } else {
#     return undef;
#   }
# }

# If the path is confined to the second quadrant, so X<=something and
# Y>=something, then has a maximum X-Y.  Presume that the x_maximum() and
# y_minimum() occur together.
#
sub diffxy_maximum {
  my ($self) = @_;
  if (defined (my $y_minimum = $self->y_minimum)
      && defined (my $x_max = $self->x_maximum)) {
    return $x_max - $y_minimum;
  } else {
    return undef;
  }
}

# absdiffxy = abs(X-Y)
sub absdiffxy_minimum {
  my ($self) = @_;
  # if X-Y all one sign, so X-Y>=0 or X-Y<=0, then abs(X-Y) from that
  my $m;
  if (defined($m = $self->diffxy_minimum) && $m >= 0) {
    return $m;
  }
  if (defined($m = $self->diffxy_maximum) && $m <= 0) {
    return - $m;
  }
  return 0;
}
sub absdiffxy_maximum {
  my ($self) = @_;
  # if X-Y constrained so min<=X-Y<=max then max abs(X-Y) one of the two ends
  if (defined (my $min = $self->diffxy_minimum)
      && defined (my $max = $self->diffxy_maximum)) {
    return _max(abs($min),abs($max));
  }
  return undef;
}


# experimental default from x_minimum(),y_minimum()
# FIXME: should use absx_minimum, absy_minimum, for paths outside first quadrant
sub rsquared_minimum {
  my ($self) = @_;

  # The X and Y each closest to the origin.  This assumes that point is
  # actually visited, but is likely to be close.
  my $x_minimum = $self->x_minimum;
  my $x_maximum = $self->x_maximum;
  my $y_minimum = $self->y_minimum;
  my $y_maximum = $self->y_maximum;
  my $x = ((  defined $x_minimum && $x_minimum) > 0 ? $x_minimum
           : (defined $x_maximum && $x_maximum) < 0 ? $x_maximum
           : 0);
  my $y = ((  defined $y_minimum && $y_minimum) > 0 ? $y_minimum
           : (defined $y_maximum && $y_maximum) < 0 ? $y_maximum
           : 0);
  return ($x*$x + $y*$y);

  # # Maybe initial point $self->n_to_xy($self->n_start)) as the default,
  # # but that's not the minimum on "wider" paths.
  # return 0;
}
use constant rsquared_maximum => undef;

sub gcdxy_minimum {
  my ($self) = @_;
  ### gcdxy_minimum(): "visited=".($self->xy_is_visited(0,0)||0)
  return ($self->xy_is_visited(0,0)
          ? 0   # gcd(0,0)=0
          : 1); # any other has gcd>=1
}
use constant gcdxy_maximum => undef;

#------------------------------------------------------------------------------

use constant dir_minimum_dxdy => (1,0);
use constant dir_maximum_dxdy => (0,0);

use constant dx_minimum => undef;
use constant dy_minimum => undef;
use constant dx_maximum => undef;
use constant dy_maximum => undef;

sub absdx_minimum {
  my ($self) = @_;
  # If dX>=0 then abs(dX)=dX always and absdx_minimum()==dx_minimum().
  # This happens for column style paths like CoprimeColumns.
  # dX>0 is only for line paths so not very interesting.
  if (defined (my $dx_minimum = $self->dx_minimum)) {
    if ($dx_minimum >= 0) { return $dx_minimum; }
  }
  return 0;
}
sub absdx_maximum {
  my ($self) = @_;
  if (defined (my $dx_minimum = $self->dx_minimum)
      && defined (my $dx_maximum = $self->dx_maximum)) {
    return _max(abs($dx_minimum),abs($dx_maximum));
  }
  return undef;
}

sub absdy_minimum {
  my ($self) = @_;
  # if dY>=0 then abs(dY)=dY always and absdy_minimum()==dy_minimum()
  if (defined (my $dy_minimum = $self->dy_minimum)) {
    if ($dy_minimum >= 0) { return $dy_minimum; }
  }
  return 0;
}
sub absdy_maximum {
  my ($self) = @_;
  if (defined (my $dy_minimum = $self->dy_minimum)
      && defined (my $dy_maximum = $self->dy_maximum)) {
    return _max(abs($dy_minimum),abs($dy_maximum));
  } else {
    return undef;
  }
}

use constant dsumxy_minimum => undef;
use constant dsumxy_maximum => undef;
use constant ddiffxy_minimum => undef;
use constant ddiffxy_maximum => undef;

#------------------------------------------------------------------------------

sub new {
  my $class = shift;
  return bless { @_ }, $class;
}

{
  my %parameter_info_hash;
  sub parameter_info_hash {
    my ($class_or_self) = @_;
    my $class = (ref $class_or_self || $class_or_self);
    return ($parameter_info_hash{$class}
            ||= { map { $_->{'name'} => $_ }
                  $class_or_self->parameter_info_list });
  }
}

sub xy_to_n_list {
  ### xy_to_n_list() ...
  if (defined (my $n = shift->xy_to_n(@_))) {
    ### $n
    return $n;
  }
  ### empty ...
  return;
}
sub xy_is_visited {
  my ($self, $x, $y) = @_;
  ### xy_is_visited(): "$x,$y is ndefined=".defined($self->xy_to_n($x,$y))
  return defined($self->xy_to_n($x,$y));
}

sub n_to_dxdy {
  my ($self, $n) = @_;
  ### n_to_dxdy(): $n
  my ($x,$y) = $self->n_to_xy ($n)
    or return;
  my ($next_x,$next_y) = $self->n_to_xy ($n + $self->arms_count)
    or return;
  ### points: "$x,$y  $next_x,$next_y"
  return ($next_x - $x,
          $next_y - $y);
}
sub n_to_rsquared {
  my ($self, $n) = @_;
  my ($x,$y) = $self->n_to_xy($n) or return undef;
  return $x*$x + $y*$y;
}
sub n_to_radius {
  my ($self, $n) = @_;
  my $rsquared = $self->n_to_rsquared($n);
  return (defined $rsquared ? sqrt($rsquared) : undef);
}

#------------------------------------------------------------------------------
# tree

use constant tree_n_parent => undef;  # default always no parent

use constant tree_n_children => ();   # default no children
sub tree_n_num_children {
  my ($self, $n) = @_;
  if ($n >= $self->n_start) {
    my @n_list = $self->tree_n_children($n);
    return scalar(@n_list);
  } else {
    return undef;
  }
}

# For non-trees n_num_children() always returns 0 so that's the single
# return here.
use constant tree_num_children_list => (0);
sub tree_num_children_minimum {
  my ($self) = @_;
  return ($self->tree_num_children_list)[0];
}
sub tree_num_children_maximum {
  my ($self) = @_;
  return ($self->tree_num_children_list)[-1];
}
sub tree_any_leaf {
  my ($self) = @_;
  return ($self->tree_num_children_minimum == 0);
}

use constant tree_n_to_subheight => 0; # default all leaf node

use constant tree_n_to_depth => undef;
use constant tree_depth_to_n => undef;
sub tree_depth_to_n_end {
  my ($self, $depth) = @_;
  if ($depth >= 0
      && defined (my $n = $self->tree_depth_to_n($depth+1))) {
    return $n-1;
  } else {
    return undef;
  }
}
sub tree_depth_to_n_range {
  my ($self, $depth) = @_;
  if (defined (my $n = $self->tree_depth_to_n($depth))
      && defined (my $n_end = $self->tree_depth_to_n_end($depth))) {
    return ($n, $n_end);
  }
  return;
}

sub tree_depth_to_width {
  my ($self, $depth) = @_;
  if (defined (my $n = $self->tree_depth_to_n($depth))
      && defined (my $n_end = $self->tree_depth_to_n_end($depth))) {
    return $n_end - $n + 1;
  }
  return undef;
}

# =item C<$bool = $path-E<gt>UNTESTED__is_tree()>
#
# Return true if C<$path> is a tree.
#
sub UNTESTED__is_tree {
  my ($self) = @_;
  return $self->tree_n_num_children($self->n_start);
}

sub tree_num_roots {
  my ($self) = @_;
  my @root_n_list = $self->tree_root_n_list;
  return scalar(@root_n_list);
}
sub tree_root_n_list {
  my ($self) = @_;
  my $n_start = $self->n_start;
  my @ret;
  for (my $n = $n_start; ; $n++) {
    # stop at non-root has a parent, or a non-tree path has no children
    if (defined($self->tree_n_parent($n))
        || ! $self->tree_n_num_children($n)) {
      last;
    }
    push @ret, $n;
  }
  return @ret;
}

# Generic search upwards.  Not fast, but works with past Toothpick or
# anything slack which doesn't have own tree_n_root().  When only one root
# there's no search.
sub tree_n_root {
  my ($self, $n) = @_;
  my $num_roots = $self->tree_num_roots;
  if ($num_roots == 0) {
    return undef;  # not a tree
  }
  my $n_start = $self->n_start;
  unless ($n >= $n_start) {  # and warn if $n==undef
    return undef;  # -inf or NaN
  }
  if ($num_roots == 1) {
    return $n_start;  # only one root, no search
  }

  for (;;) {
    my $n_parent = $self->tree_n_parent($n);
    if (! defined $n_parent) {
      return $n; # found root
    }
    unless ($n_parent < $n) {
      return undef;  # +inf or something bad not making progress
    }
    $n = $n_parent;
  }
}

# Generic search for where no more children.
# But must watch out for infinite lets, and might also watch out for
# rounding or overflow.
#
# sub path_tree_n_to_subheight {
#   my ($path, $n) = @_;
#   ### path_tree_n_to_subheight(): "$n"
#
#   if (is_infinite($n)) {
#     return $n;
#   }
#   my $max = $path->tree_n_to_depth($n) + 10;
#   my @n = ($n);
#   my $height = 0;
#   do {
#     @n = map {$path->tree_n_children($_)} @n
#       or return $height;
#     $height++;
#   } while (@n && $height < $max);
#
#   ### height infinite ...
#   return undef;
# }

#------------------------------------------------------------------------------
# shared internals

sub _max {
  my $max = 0;
  foreach my $i (1 .. $#_) {
    if ($_[$i] > $_[$max]) {
      $max = $i;
    }
  }
  return $_[$max];
}
sub _min {
  my $min = 0;
  foreach my $i (1 .. $#_) {
    if ($_[$i] < $_[$min]) {
      $min = $i;
    }
  }
  return $_[$min];
}

use Math::PlanePath::Base::Generic 'round_nearest';
sub _rect_for_first_quadrant {
  my ($self, $x1,$y1, $x2,$y2) = @_;
  $x1 = round_nearest($x1);
  $y1 = round_nearest($y1);
  $x2 = round_nearest($x2);
  $y2 = round_nearest($y2);
  ($x1,$x2) = ($x2,$x1) if $x1 > $x2;
  ($y1,$y2) = ($y2,$y1) if $y1 > $y2;
  if ($x2 < 0 || $y2 < 0) {
    return;
  }
  return ($x1,$y1, $x2,$y2);
}

# return ($quotient, $remainder)
sub _divrem {
  my ($n, $d) = @_;
  if (ref $n && $n->isa('Math::BigInt')) {
    my ($quot,$rem) = $n->copy->bdiv($d);
    if (! ref $d || $d < 1_000_000) {
      $rem = $rem->numify;  # plain remainder if fits
    }
    return ($quot, $rem);
  }
  my $rem = $n % $d;
  return (int(($n-$rem)/$d), # exact division stays in UV
          $rem);
}

# return $remainder, modify $n
# the scalar $_[0] is modified, but if it's a BigInt then a new BigInt is made
# and stored there, the bigint value is not changed
sub _divrem_mutate {
  my $d = $_[1];
  my $rem;
  if (ref $_[0] && $_[0]->isa('Math::BigInt')) {
    ($_[0], $rem) = $_[0]->copy->bdiv($d);  # quot,rem in array context
    if (! ref $d || $d < 1_000_000) {
      return $rem->numify;  # plain remainder if fits
    }
  } else {
    $rem = $_[0] % $d;
    $_[0] = int(($_[0]-$rem)/$d); # exact division stays in UV
  }
  return $rem;
}

1;
__END__

=for stopwords PlanePath Ryde Math-PlanePath Math-PlanePath-Toothpick 7-gonals 8-gonal (step+2)-gonal heptagonals octagonals bignum multi-arm eg PerlMagick NaN NaNs subclasses incrementing arrayref hashref filename enum radix ie dX dY dX,dY Rsquared radix SUBCLASSING Ns onwards supremum radix radix-1 octant dSum dDiffXY RSquared

=head1 NAME

Math::PlanePath -- points on a path through the 2-D plane

=head1 SYNOPSIS

 use Math::PlanePath;
 # only a base class, see the subclasses for actual operation

=head1 DESCRIPTION

This is a base class for some mathematical paths which map an integer
position C<$n> to and from coordinates C<$x,$y> in the 2D plane.

The current classes include the following.  The intention is that any
C<Math::PlanePath::Something> is a PlanePath, and supporting base classes or
related things are further down like C<Math::PlanePath::Base::Xyzzy>.

=for my_pod list begin

    SquareSpiral           four-sided spiral
    PyramidSpiral          square base pyramid
    TriangleSpiral         equilateral triangle spiral
    TriangleSpiralSkewed   equilateral skewed for compactness
    DiamondSpiral          four-sided spiral, looping faster
    PentSpiral             five-sided spiral
    PentSpiralSkewed       five-sided spiral, compact
    HexSpiral              six-sided spiral
    HexSpiralSkewed        six-sided spiral skewed for compactness
    HeptSpiralSkewed       seven-sided spiral, compact
    AnvilSpiral            anvil shape
    OctagramSpiral         eight pointed star
    KnightSpiral           an infinite knight's tour
    CretanLabyrinth        7-circuit extended infinitely

    SquareArms             four-arm square spiral
    DiamondArms            four-arm diamond spiral
    AztecDiamondRings      four-sided rings
    HexArms                six-arm hexagonal spiral
    GreekKeySpiral         square spiral with Greek key motif
    MPeaks                 "M" shape layers

    SacksSpiral            quadratic on an Archimedean spiral
    VogelFloret            seeds in a sunflower
    TheodorusSpiral        unit steps at right angles
    ArchimedeanChords      unit chords on an Archimedean spiral
    MultipleRings          concentric circles
    PixelRings             concentric rings of midpoint pixels
    FilledRings            concentric rings of pixels
    Hypot                  points by distance
    HypotOctant            first octant points by distance
    TriangularHypot        points by triangular distance
    PythagoreanTree        X^2+Y^2=Z^2 by trees

    PeanoCurve             3x3 self-similar quadrant
    WunderlichSerpentine   transpose parts of PeanoCurve
    HilbertCurve           2x2 self-similar quadrant
    HilbertSpiral          2x2 self-similar whole-plane
    ZOrderCurve            replicating Z shapes
    GrayCode               Gray code splits
    WunderlichMeander      3x3 "R" pattern quadrant
    BetaOmega              2x2 self-similar half-plane
    AR2W2Curve             2x2 self-similar of four parts
    KochelCurve            3x3 self-similar of two parts
    DekkingCurve           5x5 self-similar, edges
    DekkingCentres         5x5 self-similar, centres
    CincoCurve             5x5 self-similar

    ImaginaryBase          replicate in four directions
    ImaginaryHalf          half-plane replicate three directions
    CubicBase              replicate in three directions
    SquareReplicate        3x3 replicating squares
    CornerReplicate        2x2 replicating "U"
    LTiling                self-simlar L shapes
    DigitGroups            digits grouped by zeros
    FibonacciWordFractal   turns by Fibonacci word bits

    Flowsnake              self-similar hexagonal tile traversal
    FlowsnakeCentres         likewise but centres of hexagons
    GosperReplicate        self-similar hexagonal tiling
    GosperIslands          concentric island rings
    GosperSide             single side or radial

    QuintetCurve           self-similar "+" traversal
    QuintetCentres           likewise but centres of squares
    QuintetReplicate       self-similar "+" tiling

    DragonCurve            paper folding
    DragonRounded          paper folding rounded corners
    DragonMidpoint         paper folding segment midpoints
    AlternatePaper         alternating direction folding
    AlternatePaperMidpoint alternating direction folding, midpoints
    TerdragonCurve         ternary dragon
    TerdragonRounded       ternary dragon rounded corners
    TerdragonMidpoint      ternary dragon segment midpoints
    R5DragonCurve          radix-5 dragon curve
    R5DragonMidpoint       radix-5 dragon curve midpoints
    CCurve                 "C" curve
    ComplexPlus            base i+realpart
    ComplexMinus           base i-realpart, including twindragon
    ComplexRevolving       revolving base i+1

    SierpinskiCurve        self-similar right-triangles
    SierpinskiCurveStair   self-similar right-triangles, stair-step
    HIndexing              self-similar right-triangles, squared up

    KochCurve              replicating triangular notches
    KochPeaks              two replicating notches
    KochSnowflakes         concentric notched 3-sided rings
    KochSquareflakes       concentric notched 4-sided rings
    QuadricCurve           eight segment zig-zag
    QuadricIslands           rings of those zig-zags
    SierpinskiTriangle     self-similar triangle by rows
    SierpinskiArrowhead    self-similar triangle connectedly
    SierpinskiArrowheadCentres  likewise but centres of triangles

    Rows                   fixed-width rows
    Columns                fixed-height columns
    Diagonals              diagonals between X and Y axes
    DiagonalsAlternating   diagonals Y to X and back again
    DiagonalsOctant        diagonals between Y axis and X=Y centre
    Staircase              stairs down from the Y to X axes
    StaircaseAlternating   stairs Y to X and back again
    Corner                 expanding stripes around a corner
    PyramidRows            expanding stacked rows pyramid
    PyramidSides           along the sides of a 45-degree pyramid
    CellularRule           cellular automaton by rule number
    CellularRule54         cellular automaton rows pattern
    CellularRule57         cellular automaton (rule 99 mirror too)
    CellularRule190        cellular automaton (rule 246 mirror too)
    UlamWarburton          cellular automaton diamonds
    UlamWarburtonQuarter   cellular automaton quarter-plane

    DiagonalRationals      rationals X/Y by diagonals
    FactorRationals        rationals X/Y by prime factorization
    GcdRationals           rationals X/Y by rows with GCD integer
    RationalsTree          rationals X/Y by tree
    FractionsTree          fractions 0<X/Y<1 by tree
    ChanTree               rationals X/Y multi-child tree
    CfracDigits            continued fraction 0<X/Y<1 by digits
    CoprimeColumns         coprime X,Y
    DivisibleColumns       X divisible by Y
    WythoffArray           Fibonacci recurrences
    PowerArray             powers in rows
    File                   points from a disk file

=for my_pod list end

And in the separate Math-PlanePath-Toothpick distribution

    ToothpickTree          pattern of toothpicks
    ToothpickReplicate     same by replication rather than tree
    ToothpickUpist         toothpicks only growing upwards
    ToothpickSpiral        toothpicks around the origin

    LCornerTree            L-shape corner growth
    LCornerReplicate       same by replication rather than tree
    OneOfEight

The paths are object oriented to allow parameters, though many have none.
See C<examples/numbers.pl> in the Math-PlanePath sources for a sample
printout of numbers from selected paths or all paths.

=head2 Number Types

The C<$n> and C<$x,$y> parameters can be either integers or floating point.
The paths are meant to do something sensible with fractions but expect
rounding-off for big floating point exponents.

Floating point infinities (when available) give NaN or infinite returns of
some kind (some unspecified kind as yet).  C<n_to_xy()> on negative infinity
is an empty return, the same as other negative C<$n>.

Floating point NaNs (when available) give NaN, infinite, or empty/undef
returns, but again of some unspecified kind as yet.

Many of the classes can operate on overloaded number types as inputs and
give corresponding outputs.

    Math::BigInt        maybe perl 5.8 up for ** operator
    Math::BigRat
    Math::BigFloat
    Number::Fraction    1.14 or higher for abs()

A few classes might truncate a bignum or a fraction to a float as yet.  In
general the intention is to make the calculations generic enough to act on
any sensible number type.  Recent enough versions of the bignum modules
might be required, perhaps C<BigInt> of Perl 5.8 or higher for C<**>
exponentiation operator.

For reference, an C<undef> input as C<$n>, C<$x>, C<$y>, etc, is meant to
provoke an uninitialized value warning when warnings are enabled, but
currently it doesn't croak etc.  Perhaps that will change, but the warning
at least prevents bad inputs going unnoticed.

=head1 FUNCTIONS

In the following C<Foo> is one of the various subclasses, see the list above
and under L</SEE ALSO>.

=head2 Constructor

=over 4

=item C<$path = Math::PlanePath::Foo-E<gt>new (key=E<gt>value, ...)>

Create and return a new path object.  Optional key/value parameters may
control aspects of the object.

=back

=head2 Coordinate Methods

=over

=item C<($x,$y) = $path-E<gt>n_to_xy ($n)>

Return X,Y coordinates of point C<$n> on the path.  If there's no point
C<$n> then the return is an empty list.  For example

    my ($x,$y) = $path->n_to_xy (-123)
      or next;   # no negatives in $path

Paths start from C<$path-E<gt>n_start()> below, though some will give a
position for N=0 or N=-0.5 too.

=item C<($dx,$dy) = $path-E<gt>n_to_dxdy ($n)>

Return the change in X and Y going from point C<$n> to point C<$n+1>, or for
paths with multiple arms from C<$n> to C<$n+$arms_count> (thus advancing one
point along the arm of C<$n>).

    +  $n+1 == $next_x,$next_y
    ^
    |
    |                    $dx = $next_x - $x
    +  $n == $x,$y       $dy = $next_y - $y

C<$n> can be fractional and in that case the dX,dY is from that fractional
C<$n> position to C<$n+1> (or C<$n+$arms>).

           frac $n+1 == $next_x,$next_y
                v
    integer *---+----
            |  /
            | /
            |/                 $dx = $next_x - $x
       frac +  $n == $x,$y     $dy = $next_y - $y
            |
    integer *

In both cases C<n_to_dxdy()> is the difference C<$dx=$next_x-$x,
$dy=$next_y-$y>.  Currently for most paths it's merely two C<n_to_xy()>
calls to calculate the two points, but some paths can calculate a dX,dY with
a little less work.

=item C<$rsquared = $path-E<gt>n_to_radius ($n)>

=item C<$rsquared = $path-E<gt>n_to_rsquared ($n)>

Return the radial distance R=sqrt(X^2+Y^2) of point C<$n>, or the radius
squared R^2=X^2+Y^2.  If there's no point C<$n> then the return is C<undef>.

For a few paths these might be calculated with less work than C<n_to_xy()>.
For example the C<SacksSpiral> is simply R^2=N, or for example the
C<MultipleRings> path with its default step=6 has an integer radius for
integer C<$n> whereas C<$x,$y> are fractional (and inexact).

=item C<$n = $path-E<gt>xy_to_n ($x,$y)>

Return the N point number at coordinates C<$x,$y>.  If there's nothing at
C<$x,$y> then return C<undef>.

    my $n = $path->xy_to_n(20,20);
    if (! defined $n) {
      next;   # nothing at this X,Y
    }

C<$x> and C<$y> can be fractional and the path classes will give an integer
C<$n> which contains C<$x,$y> within a unit square, circle, or intended
figure centred on the integer C<$n>.

For paths which completely fill the plane there's always an C<$n> to return,
but for the spread-out paths an C<$x,$y> position may fall in between (no
C<$n> close enough) and give C<undef>.

=item C<@n_list = $path-E<gt>xy_to_n_list ($x,$y)>

Return a list of N point numbers at coordinates C<$x,$y>.  If there's
nothing at C<$x,$y> then return an empty list.

    my @n_list = $path->xy_to_n(20,20);

Most paths have just a single N for a given X,Y but some such as
C<DragonCurve> and C<TerdragonCurve> have multiple N's at a given X,Y and
this method returns all of them.

=item C<$bool = $path-E<gt>xy_is_visited ($x,$y)>

Return true if C<$x,$y> is visited.  This is equivalent to

    defined($path->xy_to_n($x,$y))

Some paths cover the plane and for them C<xy_is_visited()> is always true.
For others it might be less work to just test a point than to calculate its
C<$n>.

=item C<($n_lo, $n_hi) = $path-E<gt>rect_to_n_range ($x1,$y1, $x2,$y2)>

Return a range of N values covering or exceeding a rectangle with corners at
C<$x1>,C<$y1> and C<$x2>,C<$y2>.  The range is inclusive.  For example,

     my ($n_lo, $n_hi) = $path->rect_to_n_range (-5,-5, 5,5);
     foreach my $n ($n_lo .. $n_hi) {
       my ($x, $y) = $path->n_to_xy($n) or next;
       print "$n  $x,$y";
     }

The return might be an over-estimate of the N range required to cover the
rectangle.  Even if the range is exact the nature of the path may mean many
points between C<$n_lo> and C<$n_hi> are outside the rectangle.  But the
range is at least a lower and upper bound on the N values which occur in the
rectangle.  Classes which can guarantee an exact lo/hi range say so in their
docs.

C<$n_hi> is usually no more than an extra partial row, revolution, or
self-similar level.  C<$n_lo> might be merely the starting
C<$path-E<gt>n_start()>, which is fine if the origin is in the desired
rectangle but away from the origin might actually start higher.

C<$x1>,C<$y1> and C<$x2>,C<$y2> can be fractional.  If they partly overlap
some N figures then those N's are included in the return.

If there's no points in the rectangle then the return can be a "crossed"
range like C<$n_lo=1>, C<$n_hi=0> (which makes a C<foreach> do no loops).
But C<rect_to_n_range()> may not always notice there's no points in the
rectangle and might instead return some over-estimate.

=back

=head2 Descriptive Methods

=over

=item C<$n = $path-E<gt>n_start()>

Return the first N in the path.  The start is usually either 0 or 1
according to what is most natural for the path.  Some paths have an
C<n_start> parameter to control the numbering.

Some classes have secret dubious undocumented support for N values below
this start (zero or negative), but C<n_start()> is the intended starting
point.

=item C<$f = $path-E<gt>n_frac_discontinuity()>

Return the fraction of N at which there may be discontinuities in the path.
For example if there's a jump in the coordinates between N=7.4999 and N=7.5
then the returned C<$f> is 0.5.  Or C<$f> is 0 if there's a discontinuity
between 6.999 and 7.0.

If there's no discontinuities in the path then the return is C<undef>.  That
means for example fractions between N=7 to N=8 give smooth continuous X,Y
values (of some kind).

This is mainly of interest for drawing line segments between N points.  If
there's discontinuities then the idea is to draw from say N=7.0 to N=7.499
and then another line from N=7.5 to N=8.

=item C<$arms = $path-E<gt>arms_count()>

Return the number of arms in a "multi-arm" path.

For example in C<SquareArms> this is 4 and each arm increments in turn, so
the first arm is N=1,5,9,13,etc starting from C<$path-E<gt>n_start()> and
incrementing by 4 each time.

=item C<$bool = $path-E<gt>x_negative()>

=item C<$bool = $path-E<gt>y_negative()>

Return true if the path extends into negative X coordinates and/or negative
Y coordinates respectively.

=item C<$bool = Math::PlanePath::Foo-E<gt>class_x_negative()>

=item C<$bool = Math::PlanePath::Foo-E<gt>class_y_negative()>

=item C<$bool = $path-E<gt>class_x_negative()>

=item C<$bool = $path-E<gt>class_y_negative()>

Return true if any paths made by this class extend into negative X
coordinates and/or negative Y coordinates, respectively.

For some classes the X or Y extent may depend on parameter values.

=item C<$x = $path-E<gt>x_minimum()>

=item C<$y = $path-E<gt>y_minimum()>

=item C<$x = $path-E<gt>x_maximum()>

=item C<$y = $path-E<gt>y_maximum()>

Return the minimum or maximum of the X or Y coordinate reached by integer N
values in the path.  If there's no minimum or maximum then return C<undef>.

=item C<$dx = $path-E<gt>dx_minimum()>

=item C<$dx = $path-E<gt>dx_maximum()>

=item C<$dy = $path-E<gt>dy_minimum()>

=item C<$dy = $path-E<gt>dy_maximum()>

Return the minimum or maximum change dX, dY occurring in the path for
integer N to N+1.  For a multi-arm path the change is N to N+arms so it's
the change along the same arm.

Various paths which go by rows have non-decreasing Y.  For them
C<dy_minimum()> is 0.

=item C<$adx = $path-E<gt>absdx_minimum()>

=item C<$adx = $path-E<gt>absdx_maximum()>

=item C<$ady = $path-E<gt>absdy_minimum()>

=item C<$ady = $path-E<gt>absdy_maximum()>

Return the minimum or maximum change abs(dX) or abs(dY) occurring in the
path for integer N to N+1.  For a multi-arm path the change is N to N+arms
so it's the change along the same arm.

C<absdx_maximum()> is simply max(dXmax,-dXmin), the biggest change either
positive or negative.  C<absdy_maximum()> similarly.

C<absdx_minimum()> is 0 if dX=0 occurs anywhere in the path, which means any
vertical step.  If X always changes then C<absdx_minimum()> will be
something bigger than 0.  C<absdy_minimum()> likewise 0 if any horizontal
dY=0, or bigger if Y always changes.

=item C<$sum = $path-E<gt>sumxy_minimum()>

=item C<$sum = $path-E<gt>sumxy_maximum()>

Return the minimum or maximum values taken by coordinate sum X+Y reached by
integer N values in the path.  If there's no minimum or maximum then return
C<undef>.

S=X+Y is an anti-diagonal.  A path which is always right and above some
anti-diagonal has a minimum.  Some paths might be entirely left and below
and so have a maximum, though that's unusual.

                          \        Path always above
                           \ |     has minimum S=X+Y
                            \|
                          ---o----
      Path always below      |\
      has maximum S=X+Y      | \
                                \  S=X+Y


=item C<$sum = $path-E<gt>sumabsxy_minimum()>

=item C<$sum = $path-E<gt>sumabsxy_maximum()>

Return the minimum or maximum values taken by coordinate sum abs(X)+abs(Y)
reached by integer N values in the path.  A minimum always exists but if
there's no maximum then return C<undef>.

SumAbs=abs(X)+abs(Y) is sometimes called the "taxi-cab" or "Manhatten"
distance, being how far to travel through a square-grid city to get to X,Y.
C<sumabsxy_minimum()> is then how close to the origin the path extends.

SumAbs can also be interpreted geometrically as numbering the anti-diagonals
of the quadrant containing X,Y, which is equivalent to asking which diamond
shape X,Y falls on.  C<sumabsxy_minimum()> is then the smallest such diamond
reached by the path.

         |
        /|\       SumAbs = which diamond X,Y falls on
       / | \
      /  |  \
    -----o-----
      \  |  /
       \ | /
        \|/
         |

=item C<$diffxy = $path-E<gt>diffxy_minimum()>

=item C<$diffxy = $path-E<gt>diffxy_maximum()>

Return the minimum or maximum values taken by coordinate difference X-Y
reached by integer N values in the path.  If there's no minimum or maximum
then return C<undef>.

D=X-Y is a leading diagonal.  A path which is always right and below such a
diagonal has a minimum, for example C<HypotOctant>.  A path which is always
left and above some diagonal has a maximum D=X-Y.  For example various
wedge-like paths such as C<PyramidRows> in its default step=2, and "upper
octant" paths have a maximum.

                                 /   D=X-Y
        Path always below     | /
        has maximum D=X-Y     |/
                           ---o----
                             /|
                            / |      Path always above
                           /         has minimum D=X-Y

=item C<$absdiffxy = $path-E<gt>absdiffxy_minimum()>

=item C<$absdiffxy = $path-E<gt>absdiffxy_maximum()>

Return the minimum or maximum values taken by abs(X-Y) for integer N in the
path.  The minimum is 0 or more.  If there's maximum then return C<undef>.

abs(X-Y) can be interpreted geometrically as the distance away from the X=Y
diagonal and measured at right-angles to that line.

     d=abs(X-Y)  X=Y line
           ^    /
            \  /
             \/
             /\
            /  \
           /    \
          o      v
         /         d=abs(X-Y)

Paths which visit the X=Y line (or approach it as an infimum) have
C<absdiffxy_minimum() = 0>.  Otherwise C<absdiffxy_minimum()> is how close
they come to the line.

If the path is entirely below the X=Y line so XE<gt>=Y then X-Y>=0 and
C<absdiffxy_minimum()> is the same as C<diffxy_minimum()>.  If the path is
entirely below the X=Y line then C<absdiffxy_minimum()> is
S<C<- diffxy_maximum()>>.

=item C<$dsumxy = $path-E<gt>dsumxy_minimum()>

=item C<$dsumxy = $path-E<gt>dsumxy_maximum()>

=item C<$ddiffxy = $path-E<gt>ddiffxy_minimum()>

=item C<$ddiffxy = $path-E<gt>ddiffxy_maximum()>

Return the minimum or maximum change dSum or dDiffXY occurring in the path
for integer N to N+1.  For a multi-arm path the change is N to N+arms so
it's the change along the same arm.

=item C<$rsquared = $path-E<gt>rsquared_minimum()>

=item C<$rsquared = $path-E<gt>rsquared_maximum()>

Return the minimum or maximum Rsquared = X^2+Y^2 reached by integer N values
in the path.  If there's no minimum or maximum then return C<undef>.

Rsquared is always E<gt>= 0 so it always has a minimum.  The minimum will be
more than 0 for paths which don't include the origin X=0,Y=0.

RSquared generally has no maximum since the paths usually extend infinitely
in some direction.  C<rsquared_maximum()> returns C<undef> in that case.

=cut

# =item C<$gcd = $path-E<gt>gcdxy_minimum()>
#
# =item C<$gcd = $path-E<gt>gcdxy_maximum()>
#
# Return the minimum or maximum GCD(X,Y) reached by integer N values in the
# path.  If there's no minimum or maximum then return C<undef>.
#
# C<gcdxy_minimum()> is always 0 or more since the sign of X and Y is ignored
# for taking the GCD.  GCD(0,0)=0 is the only GCD=0.  X!=0 or Y!=0 gives
# GCD(X,Y)E<gt>0.  So the minimum is 0 if X=0,Y=0 is visited and E<gt>0 if
# not.
#
# C<gcdxy_maximum()> is usually C<undef> since there's no limit to the GCD.
# Paths such as C<CoprimeColumns> where X,Y have no common factor have
# C<gcdxy_maximum()> returning 1.

=pod

=item C<($dx,$dy) = $path-E<gt>dir_minimum_dxdy()>

=item C<($dx,$dy) = $path-E<gt>dir_maximum_dxdy()>

Return a vector which is the minimum or maximum angle taken by a step
integer N to N+1, or for a multi-arm path N to N+arms so it's the change
along the same arm.  Directions are reckoned anti-clockwise around from the
X axis.

                  |  *  dX=2,dY=2
    dX=-1,dY=1  * | /
                 \|/
            ------+----*  dX=1,dY=0
                  |
                  |
                  * dX=0,dY=-1

A path which is always goes N,S,E,W such as the C<SquareSpiral> has minimum
East dX=1,dY=0 and maximum South dX=0,dY=-1.

Paths which go diagonally may have different limits.  For example the
C<KnightSpiral> goes in 2x1 steps and so has minimum East-North-East
dX=2,dY=1 and maximum East-South-East dX=2,dY=-1.

If the path has directions approaching 360 degrees then
C<dir_maximum_dxdy()> is 0,0 to mean a full circle as a supremum.  For
example C<MultipleRings>.

If the path only ever goes East then the maximum is East dX=1,dY=0, and the
minimum the same.  This isn't particularly interesting, but arises for
example in the C<Columns> path height=0.

=item C<$str = $path-E<gt>figure()>

Return a string name of the figure (shape) intended to be drawn at each
C<$n> position.  This is currently either

    "square"     side 1 centred on $x,$y
    "circle"     diameter 1 centred on $x,$y

Of course this is only a suggestion since PlanePath doesn't draw anything
itself.  A figure like a diamond for instance can look good too.

=back

=head2 Tree Methods

Some paths are structured like a tree where each N has a parent and possibly
some children.

                 123
                / | \
             456 999 458
            /        / \
          1000    1001 1005

The N numbering and any relation to X,Y positions varies among the paths.
Some are numbered by rows in breadth-first style and some have children with
X,Y positions adjacent to their parent, but that shouldn't be assumed, only
that there's a parent-child relation down from some set of top nodes.

=over

=item C<@n_children = $path-E<gt>tree_n_children($n)>

Return a list of N values which are the child nodes of C<$n>, or return an
empty list if C<$n> has no children.

There could be no children either because C<$path> is not a tree or because
there's no children at a particular C<$n>.

=item C<$num = $path-E<gt>tree_n_num_children($n)>

Return the number of children of C<$n>, or 0 if C<$n> has no children, or
C<undef> if S<C<$n E<lt> n_start()>> (ie. before the start of the path).

If the tree is considered as a directed graph then this is the "out-degree"
of C<$n>.

=item C<$n_parent = $path-E<gt>tree_n_parent($n)>

Return the parent node of C<$n>, or C<undef> if it has no parent.

There is no parent at the top of the tree, or one of multiple tops, or if
C<$path> is not a tree.

=item C<$n_root = $path-E<gt>tree_n_root ($n)>

Return the N which is root node of C<$n>.  This is the top of the tree as
by following C<tree_n_parent()> repeatedly until no more parent.

The return is C<undef> if there's no such C<$n> or C<$path> is not a tree.

=item C<$depth = $path-E<gt>tree_n_to_depth($n)>

Return the depth of node C<$n>, or C<undef> if there's no point C<$n>.  The
top of the tree is depth=0, then its children are depth=1, etc.

The depth is a count of how many parent, grandparent, etc, levels are above
C<$n>, ie. until reaching C<tree_n_to_parent()> returning C<undef>.  For
non-tree paths C<tree_n_to_parent()> is always C<undef> and
C<tree_n_to_depth()> is always 0.

=item C<$n_lo = $path-E<gt>tree_depth_to_n($depth)>

=item C<$n_hi = $path-E<gt>tree_depth_to_n_end($depth)>

=item C<($n_lo, $n_hi) = $path-E<gt>tree_depth_to_n_range ($depth)>

Return the first or last N, or both those N, for tree level C<$depth> in the
path.  If there's no such C<$depth> or C<$path> is not a tree then return
C<undef>, or for C<tree_depth_to_n_range()> return an empty list.

The points C<$n_lo> through C<$n_hi> might not necessarily all be at
C<$depth>.  It's possible for depths to be interleaved or intermixed in the
point numbering.  But many paths are breadth-wise successive rows and for
them C<$n_lo> to C<$n_hi> inclusive is all C<$depth>.

C<$n_hi> can only exist if the row has a finite number of points.  That's
true of all current paths, but perhaps allowance should be made for C<$n_hi>
as C<undef> or some such if there is no maximum N for some row.

=item C<$num = $path-E<gt>tree_depth_to_width ($depth)>

Return the number of points at C<$depth> in the tree.  If there's no such
C<$depth> or C<$path> is not a tree then return C<undef>.

=item C<$height = $path-E<gt>tree_n_to_subheight($n)>

Return the height of the sub-tree starting at C<$n>, or C<undef> if
infinite.  The height of a tree is the longest distance down to a leaf node.
For example,

    ...                      N     subheight
      \                     ---    ---------
       6    7   8            0       undef
        \    \ /             1       undef
         3    4   5          2         2
          \    \ /           3       undef
           1    2            4         1
            \  /             5         0
              0             ...

At N=0 and all the left side the tree continues infinitely so the sub-height
is infinite (so C<undef>).  For N=2 the sub-height is 2 because the longest
path down is 2 levels (to N=4 then N=7 or N=8).  For a leaf node such as N=5
the sub-height is 0.

=back

=head2 Tree Descriptive Methods

=over

=item C<$num = $path-E<gt>tree_num_roots()>

Return the number of root nodes in C<$path>.  If C<$path> is not a tree then
return 0.  Many tree paths have a single root and for them the return is 1.

=item C<@n_list = $path-E<gt>tree_root_n_list()>

Return a list of the N values which are the root nodes in C<$path>.  If
C<$path> is not a tree then this is an empty list.  There are
C<tree_num_roots()> many return values.

=item C<$num = $path-E<gt>tree_num_children_minimum()>

=item C<$num = $path-E<gt>tree_num_children_maximum()>

=item C<@nums = $path-E<gt>tree_num_children_list()>

Return the possible number of children of the nodes of C<$path>, either the
minimum, maximum, or a list of all possible number of children.

For C<tree_num_children_list()> the list of values is in increasing order,
so the first value is C<tree_num_children_minimum()> and the last is
C<tree_num_children_maximum()>.

=item C<$bool = $path-E<gt>tree_any_leaf()>

Return true if there are any leaf nodes in the tree, meaning any N for which
C<tree_n_num_children()> is 0.

This is the same as C<tree_num_children_minimum()==0> since if NumChildren=0
occurs then there are leaf nodes.

Some trees may have no leaf nodes, for example in the complete binary tree
of C<RationalsTree> every node always has 2 children.

=back

=head2 Parameter Methods

=over

=item C<$aref = Math::PlanePath::Foo-E<gt>parameter_info_array()>

=item C<@list = Math::PlanePath::Foo-E<gt>parameter_info_list()>

Return an arrayref of list describing the parameters taken by a given class.
This meant to help making widgets etc for user interaction in a GUI.  Each
element is a hashref

    {
      name        =>    parameter key arg for new()
      share_key   =>    string, or undef
      description =>    human readable string
      type        =>    string "integer","boolean","enum" etc
      default     =>    value
      minimum     =>    number, or undef
      maximum     =>    number, or undef
      width       =>    integer, suggested display size
      choices     =>    for enum, an arrayref
    }

C<type> is a string, one of

    "integer"
    "enum"
    "boolean"
    "string"
    "filename"

"filename" is separate from "string" since it might require subtly different
handling to reach Perl as a byte string, whereas a "string" type might in
principle take Perl wide chars.

For "enum" the C<choices> field is the possible values, such as

    { name => "flavour",
      type => "enum",
      choices => ["strawberry","chocolate"],
    }

C<minimum> and/or C<maximum> are omitted if there's no hard limit on the
parameter.

C<share_key> is designed to indicate when parameters from different
C<PlanePath> classes can done by a single control widget in a GUI etc.
Normally the C<name> is enough, but when the same name has slightly
different meanings in different classes a C<share_key> allows the same
meanings to be matched up.

=item C<$hashref = Math::PlanePath::Foo-E<gt>parameter_info_hash()>

Return a hashref mapping parameter names C<$info-E<gt>{'name'}> to their
C<$info> records.

    { wider => { name => "wider",
                 type => "integer",
                 ...
               },
    }

=back

=head1 GENERAL CHARACTERISTICS

The classes are mostly based on integer C<$n> positions and those designed
for a square grid turn an integer C<$n> into integer C<$x,$y>.  Usually they
give in-between positions for fractional C<$n> too.  Classes not on a square
grid but instead giving fractional X,Y such as C<SacksSpiral> and
C<VogelFloret> are designed for a unit circle at each C<$n> but they too can
give in-between positions on request.

All X,Y positions are calculated by separate C<n_to_xy()> calls.  To follow
a path use successive C<$n> values starting from C<$path-E<gt>n_start()>.

    foreach my $n ($path->n_start .. 100) {
      my ($x,$y) = $path->n_to_xy($n);
      print "$n  $x,$y\n";
    }

The separate C<n_to_xy()> calls were motivated by plotting just some N
points of a path, such as just the primes or the perfect squares.
Successive positions in paths could perhaps be done more efficiently in an
iterator style.  Paths with a quadratic "step" are not much worse than a
C<sqrt()> to break N into a segment and offset, but the self-similar paths
which chop N into digits of some radix could increment instead of
recalculate.

If interested only in a particular rectangle or similar region then
iterating has the disadvantage that it may stray outside the target region
for a long time, making an iterator much less useful than it seems.  For
wild paths it can be better to apply C<xy_to_n()> by rows or similar across
the desired region.

L<Math::NumSeq::PlanePathCoord> etc offer the PlanePath coordinates,
directions, turns, etc as sequences.  The iterator forms there simply make
repeated calls to C<n_to_xy()> etc.

=head2 Scaling and Orientation

The paths generally make a first move to the right and go anti-clockwise
around from the X axis, unless there's some more natural orientation.
Anti-clockwise is the usual direction for mathematical spirals.

There's no parameters for scaling, offset or reflection as those things are
thought better left to a general coordinate transformer, for example to
expand or invert for display.  Some easy transformations can be had just
from the X,Y with

    -X,Y        flip horizontally (mirror image)
    X,-Y        flip vertically (across the X axis)

    -Y,X        rotate +90 degrees  (anti-clockwise)
    Y,-X        rotate -90 degrees  (clockwise)
    -X,-Y       rotate 180 degrees

Flip vertically makes spirals go clockwise instead of anti-clockwise, or a
flip horizontally the same but starting on the left at the negative X axis.
See L</Triangular Lattice> below for 60 degree rotations of the triangular
grid paths too.

The Rows and Columns paths are exceptions to the rule of not having rotated
versions of paths.  They began as ways to pass in width and height as
generic parameters and let the path use the one or the other.

For scaling and shifting see for example L<Transform::Canvas>, and to rotate
as well see L<Geometry::AffineTransform>.

=head2 Loop Step

The paths can be characterized by how much longer each loop or repetition is
than the preceding one.  For example each cycle around the C<SquareSpiral>
is 8 more N points than the preceding.

=for my_pod step begin

      Step        Path
      ----        ----
        0       Rows, Columns (fixed widths)
        1       Diagonals
       2/2      DiagonalsOctant (2 rows for +2)
        2       SacksSpiral, PyramidSides, Corner, PyramidRows (default)
        4       DiamondSpiral, AztecDiamondRings, Staircase
       4/2      CellularRule54, CellularRule57,
                  DiagonalsAlternating (2 rows for +4)
        5       PentSpiral, PentSpiralSkewed
       5.65     PixelRings (average about 4*sqrt(2))
        6       HexSpiral, HexSpiralSkewed, MPeaks,
                  MultipleRings (default)
       6/2      CellularRule190 (2 rows for +6)
       6.28     ArchimedeanChords (approaching 2*pi),
                  FilledRings (average 2*pi)
        7       HeptSpiralSkewed
        8       SquareSpiral, PyramidSpiral
      16/2      StaircaseAlternating (up and back for +16)
        9       TriangleSpiral, TriangleSpiralSkewed
       12       AnvilSpiral
       16       OctagramSpiral, ToothpickSpiral
      19.74     TheodorusSpiral (approaching 2*pi^2)
      32/4      KnightSpiral (4 loops 2-wide for +32)
       64       DiamondArms (each arm)
       72       GreekKeySpiral
      128       SquareArms (each arm)
     128/4      CretanLabyrinth (4 loops for +128)
      216       HexArms (each arm)

    totient     CoprimeColumns, DiagonalRationals
    numdivisors DivisibleColumns
    various     CellularRule

    parameter   MultipleRings, PyramidRows

=for my_pod step end

The step determines which quadratic number sequences make straight lines.
For example the gap between successive perfect squares increases by 2 each
time (4 to 9 is +5, 9 to 16 is +7, 16 to 25 is +9, etc), so the perfect
squares make a straight line in the paths of step 2.

In general straight lines on stepped paths are quadratics

   N = a*k^2 + b*k + c    where a=step/2

The polygonal numbers are like this, with the (step+2)-gonal numbers making
a straight line on a "step" path.  For example the 7-gonals (heptagonals)
are 5/2*k^2-3/2*k and make a straight line on the step=5 C<PentSpiral>.  Or
the 8-gonal octagonal numbers 6/2*k^2-4/2*k on the step=6 C<HexSpiral>.

There are various interesting properties of primes in quadratic
progressions.  Some quadratics seem to have more primes than others.  For
example see L<Math::PlanePath::PyramidSides/Lucky Numbers of Euler>.  Many
quadratics have no primes at all, or none above a certain point, either
trivially if always a multiple of 2 etc, or by a more sophisticated
reasoning.  See L<Math::PlanePath::PyramidRows/Step 3 Pentagonals> for a
factorization on the roots making a no-primes gap.

A 4*step path splits a straight line in two, so for example the perfect
squares are a straight line on the step=2 "Corner" path, and then on the
step=8 C<SquareSpiral> they instead fall on two lines (lower left and upper
right).  In the bigger step there's one line of the even squares (2k)^2 ==
4*k^2 and another of the odd squares (2k+1)^2.  The gap between successive
even squares increases by 8 each time and likewise between odd squares.

=head2 Self-Similar Powers

The self-similar patterns such as C<PeanoCurve> generally have a base
pattern which repeats at powers N=base^level or squares N=(base*base)^level.
Or some multiple or relationship to such a power for things like
C<KochPeaks> and C<GosperIslands>.

=for my_pod base begin

    Base          Path
    ----          ----
      2         HilbertCurve, HilbertSpiral, ZOrderCurve (default),
                  GrayCode (default), BetaOmega, AR2W2Curve,
                  SierpinskiCurve, HIndexing, SierpinskiCurveStair,
                  ImaginaryBase (default), ImaginaryHalf (default),
                  CubicBase (default) CornerReplicate,
                  ComplexMinus (default), ComplexPlus (default),
                  ComplexRevolving, DragonCurve, DragonRounded,
                  DragonMidpoint, AlternatePaper, AlternatePaperMidpoint,
                  CCurve, DigitGroups (default), PowerArray (default)
      3         PeanoCurve (default), WunderlichSerpentine (default),
                  WunderlichMeander, KochelCurve,
                  GosperIslands, GosperSide
                  SierpinskiTriangle, SierpinskiArrowhead,
                  SierpinskiArrowheadCentres,
                  TerdragonCurve, TerdragonRounded, TerdragonMidpoint,
                  UlamWarburton, UlamWarburtonQuarter (each level)
      4         KochCurve, KochPeaks, KochSnowflakes, KochSquareflakes,
                  LTiling,
      5         QuintetCurve, QuintetCentres, QuintetReplicate,
                  DekkingCurve, DekkingCentres, CincoCurve,
                  R5DragonCurve, R5DragonMidpoint
      7         Flowsnake, FlowsnakeCentres, GosperReplicate
      8         QuadricCurve, QuadricIslands
      9         SquareReplicate
    Fibonacci   FibonacciWordFractal, WythoffArray
    parameter   PeanoCurve, WunderlichSerpentine, ZOrderCurve, GrayCode,
                  ImaginaryBase, ImaginaryHalf, CubicBase, ComplexPlus,
                  ComplexMinus, DigitGroups, PowerArray

=for my_pod base end

Many number sequences plotted on these self-similar paths tend to be fairly
random, or merely show the tiling or path layout rather than much about the
number sequence.  Sequences related to the base can make holes or patterns
picking out parts of the path.  For example numbers without a particular
digit (or digits) in the relevant base show up as holes.  See for example
L<Math::PlanePath::ZOrderCurve/Power of 2 Values>.

=head2 Triangular Lattice

Some paths are on triangular or "A2" lattice points like

      *---*---*---*---*---*
     / \ / \ / \ / \ / \ /
    *---*---*---*---*---*
     \ / \ / \ / \ / \ / \
      *---*---*---*---*---*
     / \ / \ / \ / \ / \ /
    *---*---*---*---*---*
     \ / \ / \ / \ / \ / \
      *---*---*---*---*---*
     / \ / \ / \ / \ / \ /
    *---*---*---*---*---*

This is done in integer X,Y on a square grid by using every second square
and offsetting alternate rows.  This means sum X+Y even, ie. X,Y either both
even or both odd, not of opposite parity.

    . * . * . * . * . * . *
    * . * . * . * . * . * .
    . * . * . * . * . * . *
    * . * . * . * . * . * .
    . * . * . * . * . * . *
    * . * . * . * . * . * .

The X axis the and diagonals X=Y and X=-Y divide the plane into six equal
parts in this grid.

       X=-Y     X=Y
         \     /
          \   /
           \ /
    ----------------- X=0
           / \
          /   \
         /     \

The diagonal X=3*Y is the middle of the first sixth, representing a twelfth
of the plane.

The resulting triangles are flatter than they should be.  The triangle base
is width=2 and top is height=1, whereas it would be height=sqrt(3) for an
equilateral triangle.  That sqrt(3) factor can be applied if desired,

    X, Y*sqrt(3)          side length 2

    X/2, Y*sqrt(3)/2      side length 1

Integer Y values have the advantage of fitting pixels on the usual kind of
raster computer screen, and not losing precision in floating point results.

If doing a general-purpose coordinate rotation then be sure to apply the
sqrt(3) scale factor before rotating or the result will be skewed.  60
degree rotations can be made within the integer X,Y coordinates directly as
follows, all giving integer X,Y results.

    (X-3Y)/2, (X+Y)/2       rotate +60   (anti-clockwise)
    (X+3Y)/2, (Y-X)/2       rotate -60   (clockwise)
    -(X+3Y)/2, (X-Y)/2      rotate +120
    (3Y-X)/2, -(X+Y)/2      rotate -120
    -X,-Y                   rotate 180

    (X+3Y)/2, (X-Y)/2       mirror across the X=3*Y twelfth line

The sqrt(3) factor can be worked into a hypotenuse radial distance
calculation as follows if comparing distances from the origin.

    hypot = sqrt(X*X + 3*Y*Y)

See for instance C<TriangularHypot> which is triangular points ordered by
this radial distance.

=head1 FORMULAS

The formulas section in the POD of each class describes some of the
calculations.  This might be of interest even if the code is not.

=head2 Triangular Calculations

For a triangular lattice the rotation formulas above allow calculations to
be done in the rectangular X,Y coordinates which are the inputs and outputs
of the PlanePath functions.  Another way is to number vertically on a 60
degree angle with coordinates i,j,

          ...
          *   *   *      2
        *   *   *       1
      *   *   *      j=0
    i=0  1   2

These coordinates are sometimes used for hexagonal grids in board games etc.
Using this internally can simplify rotations a little,

    -j, i+j         rotate +60   (anti-clockwise)
    i+j, -i         rotate -60   (clockwise)
    -i-j, i         rotate +120
    j, -i-j         rotate -120
    -i, -j          rotate 180

Conversions between i,j and the rectangular X,Y are

    X = 2*i + j         i = (X-Y)/2
    Y = j               j = Y

A third coordinate k at a +120 degrees angle can be used too,

     k=0  k=1 k=2
        *   *   *
          *   *   *
            *   *   *
             0   1   2

This is redundant in that it doesn't number anything i,j alone can't
already, but it has the advantage of turning rotations into just sign
changes and swaps,

    -k, i, j        rotate +60
    j, k, -i        rotate -60
    -j, -k, i       rotate +120
    k, -i, -j       rotate -120
    -i, -j, -k      rotate 180

The conversions between i,j,k and the rectangular X,Y are like the i,j above
but with k worked in too.

    X = 2i + j - k        i = (X-Y)/2        i = (X+Y)/2
    Y = j + k             j = Y         or   j = 0
                          k = 0              k = Y

=head2 N to dX,dY -- Fractional

C<n_to_dxdy()> is the change from N to N+1, and is designed both for integer
N and fractional N.  For fractional N it can be convenient to calculate a
dX,dY at floor(N) and at floor(N)+1 and then combine the two in proportion
to frac(N).

                     int+2
                      |
                      |
                      N+1    \
                     /|       |
                    / |       |
                   /  |       | frac
                  /   |       |
                 /    |       |
                /     |      /
       int-----N------int+1
    this_dX  dX,dY     next_dX
    this_dY            next_dY

       |-------|------|
         frac   1-frac


    int = int(N)
    frac = N - int    0 <= frac < 1

    this_dX,this_dY  at int
    next_dX,next_dY  at int+1

    at fractional N
      dX = this_dX * (1-frac) + next_dX * frac
      dY = this_dY * (1-frac) + next_dY * frac

This is combination of this_dX,this_dY and next_dX,next_dY in proportion to
the distances from positions N to int+1 and from int+1 to N+1.

The formulas can be rearranged to

    dX = this_dX + frac*(next_dX - this_dX)
    dY = this_dY + frac*(next_dY - this_dY)

which is like dX,dY at the integer position plus fractional part of a turn
or change to the next dX,dY.

=head2 N to dX,dY -- Self-Similar

For most of the self-similar paths such as C<HilbertCurve> the change dX,dY
is determined by following the state table transitions down through either
all digits of N, or to the last non-9 digit, ie. drop any low digits equal
to radix-1.

Generally paths which are the edges of some tiling use all digits, and those
which are the centres of a tiling stop at the lowest non-9.  This can be
seen for example in the C<DekkingCurve> using all digits, whereas its
C<DekkingCentres> variant stops at the lowest non-24.

Perhaps this all-digits vs low-non-9 even characterizes path style as edges
or centres of a tiling, when a path is specified in some way that a tiling
is not quite obvious.

=head1 SUBCLASSING

The mandatory methods for a PlanePath subclass are

    n_to_xy()
    xy_to_n()
    xy_to_n_list()     if multiple N's map to an X,Y
    rect_to_n_range()

It sometimes happens that one of C<n_to_xy()> or C<xy_to_n()> is easier than
the other but both should be implemented.

C<n_to_xy()> should do something sensible on fractional N.  The suggestion
is to make it an X,Y proportionally between integer N positions.  It can be
along a straight line or an arc as best suits the path.  A straight line can
be done simply by two calculations of the surrounding integer points, until
it's clear how to work the fraction into the code directly.

C<xy_to_n_list()> has a base implementation calling plain C<xy_to_n()> to
give a single N at X,Y.  If a path has multiple Ns at an X,Y
(eg. C<DragonCurve>) then it should implement C<xy_to_n_list()> to return
all those Ns and also implement a plain C<xy_to_n()> returning the first of
them.

C<rect_to_n_range()> can initially be any convenient over-estimate.  It
should give N big enough that from there onwards all points are sure to be
beyond the given X,Y rectangle.

The following descriptive methods have base implementations

    n_start()           1
    class_x_negative()  \ 1, so whole plane
    class_y_negative()  /
    x_negative()        calls class_x_negative()
    y_negative()        calls class_x_negative()

The base C<n_start()> starts at N=1.  Paths which treat N as digits of some
radix or where there's self-similar replication are often best started from
N=0 instead since doing so puts nice powers-of-2 etc on the axes or
diagonals.

    use constant n_start => 0;    # digit or replication style

Paths which use only parts of the plane should define C<class_x_negative()>
and/or C<class_y_negative()> to false.  For example if only the first
quadrant XE<gt>=0,YE<gt>=0 then

    use constant class_x_negative => 0;
    use constant class_y_negative => 0;

If negativeness varies with path parameters then C<x_negative()> and/or
C<y_negative()> follow those parameters and the C<class_()> forms are
whether any set of parameters ever gives negative.

The following methods have base implementations calling C<n_to_xy()>.
A subclass can implement them directly if they can be done more efficiently.

    n_to_dxdy()           calls n_to_xy() twice
    n_to_rsquared()       calls n_to_xy()
    n_to_radius()         sqrt of n_to_rsquared()

C<SacksSpiral> is an example of an easy C<n_to_rsquared()>.  Or
C<TheodorusSpiral> is only slightly trickier.  Unless a path has some sort
of easy X^2+Y^2 then it might as well let the base implementation call
C<n_to_xy()>.

The way C<n_to_dxdy()> supports fractional N can be a little tricky.  One
way is to calculate on the integers below and above and combine as described
in L</N to dX,dY -- Fractional>.  For some paths the calculation of turn or
direction at ceil(N) can be worked into a calculation of the direction at
floor(N) so taking not much more work.

The following method has a base implementation calling C<xy_to_n()>.
A subclass can implement is directly if it can be done more efficiently.

    xy_is_visited()     defined(xy_to_n($x,$y))

Paths such as C<SquareSpiral> which fill the plane have C<xy_is_visited()>
always true, so for them

    use constant xy_is_visited => 1;

For a tree path the following methods are mandatory

    tree_n_parent()
    tree_n_children()
    tree_n_to_depth()
    tree_depth_to_n()
    tree_num_children_list()
    tree_n_to_subheight()

The other tree methods have base implementations,

=over

=item C<tree_n_num_children()>

Calls C<tree_n_children()> and counts the number of return values.  Many
trees can count the children with less work than calculating outright, for
example C<RationalsTree> is simply always 2 for NE<gt>=Nstart.

=item C<tree_depth_to_n_end()>

Calls C<tree_depth_to_n($depth+1)-1>.  This assumes that the depth level
ends where the next begins.  This is true for the various breadth-wise tree
traversals, but anything interleaved etc will need its own implementation.

=item C<tree_depth_to_n_range()>

Calls C<tree_depth_to_n()> and C<tree_depth_to_n_end()>.  For some paths the
row start and end, or start and width, might be calculated together more
efficiently.

=item C<tree_depth_to_width()>

Returns C<tree_depth_to_n_end() - tree_depth_to_n() + 1>.  This suits
breadth-wise style paths where all points at C<$depth> are in a contiguous
block.  Any path not like that will need its own C<tree_depth_to_width()>.

=item C<tree_num_children_minimum()>, C<tree_num_children_maximum()>

Return the first and last values of C<tree_num_children_list()> as the
minimum and maximum.

=item C<tree_any_leaf()>

Calls C<tree_num_children_minimum()>.  If the minimum C<num_children> is 0
then there's leaf nodes.

=back

=head1 SEE ALSO

=for my_pod see_also begin

L<Math::PlanePath::SquareSpiral>,
L<Math::PlanePath::PyramidSpiral>,
L<Math::PlanePath::TriangleSpiral>,
L<Math::PlanePath::TriangleSpiralSkewed>,
L<Math::PlanePath::DiamondSpiral>,
L<Math::PlanePath::PentSpiral>,
L<Math::PlanePath::PentSpiralSkewed>,
L<Math::PlanePath::HexSpiral>,
L<Math::PlanePath::HexSpiralSkewed>,
L<Math::PlanePath::HeptSpiralSkewed>,
L<Math::PlanePath::AnvilSpiral>,
L<Math::PlanePath::OctagramSpiral>,
L<Math::PlanePath::KnightSpiral>,
L<Math::PlanePath::CretanLabyrinth>

L<Math::PlanePath::HexArms>,
L<Math::PlanePath::SquareArms>,
L<Math::PlanePath::DiamondArms>,
L<Math::PlanePath::AztecDiamondRings>,
L<Math::PlanePath::GreekKeySpiral>,
L<Math::PlanePath::MPeaks>

L<Math::PlanePath::SacksSpiral>,
L<Math::PlanePath::VogelFloret>,
L<Math::PlanePath::TheodorusSpiral>,
L<Math::PlanePath::ArchimedeanChords>,
L<Math::PlanePath::MultipleRings>,
L<Math::PlanePath::PixelRings>,
L<Math::PlanePath::FilledRings>,
L<Math::PlanePath::Hypot>,
L<Math::PlanePath::HypotOctant>,
L<Math::PlanePath::TriangularHypot>,
L<Math::PlanePath::PythagoreanTree>

L<Math::PlanePath::PeanoCurve>,
L<Math::PlanePath::WunderlichSerpentine>,
L<Math::PlanePath::WunderlichMeander>,
L<Math::PlanePath::HilbertCurve>,
L<Math::PlanePath::HilbertSpiral>,
L<Math::PlanePath::ZOrderCurve>,
L<Math::PlanePath::GrayCode>,
L<Math::PlanePath::AR2W2Curve>,
L<Math::PlanePath::BetaOmega>,
L<Math::PlanePath::KochelCurve>,
L<Math::PlanePath::DekkingCurve>,
L<Math::PlanePath::DekkingCentres>,
L<Math::PlanePath::CincoCurve>

L<Math::PlanePath::ImaginaryBase>,
L<Math::PlanePath::ImaginaryHalf>,
L<Math::PlanePath::CubicBase>,
L<Math::PlanePath::SquareReplicate>,
L<Math::PlanePath::CornerReplicate>,
L<Math::PlanePath::LTiling>,
L<Math::PlanePath::DigitGroups>,
L<Math::PlanePath::FibonacciWordFractal>

L<Math::PlanePath::Flowsnake>,
L<Math::PlanePath::FlowsnakeCentres>,
L<Math::PlanePath::GosperReplicate>,
L<Math::PlanePath::GosperIslands>,
L<Math::PlanePath::GosperSide>

L<Math::PlanePath::QuintetCurve>,
L<Math::PlanePath::QuintetCentres>,
L<Math::PlanePath::QuintetReplicate>

L<Math::PlanePath::KochCurve>,
L<Math::PlanePath::KochPeaks>,
L<Math::PlanePath::KochSnowflakes>,
L<Math::PlanePath::KochSquareflakes>

L<Math::PlanePath::QuadricCurve>,
L<Math::PlanePath::QuadricIslands>

L<Math::PlanePath::SierpinskiCurve>,
L<Math::PlanePath::SierpinskiCurveStair>,
L<Math::PlanePath::HIndexing>

L<Math::PlanePath::SierpinskiTriangle>,
L<Math::PlanePath::SierpinskiArrowhead>,
L<Math::PlanePath::SierpinskiArrowheadCentres>

L<Math::PlanePath::DragonCurve>,
L<Math::PlanePath::DragonRounded>,
L<Math::PlanePath::DragonMidpoint>,
L<Math::PlanePath::AlternatePaper>,
L<Math::PlanePath::AlternatePaperMidpoint>,
L<Math::PlanePath::TerdragonCurve>,
L<Math::PlanePath::TerdragonRounded>,
L<Math::PlanePath::TerdragonMidpoint>,
L<Math::PlanePath::R5DragonCurve>,
L<Math::PlanePath::R5DragonMidpoint>,
L<Math::PlanePath::CCurve>

L<Math::PlanePath::ComplexPlus>,
L<Math::PlanePath::ComplexMinus>,
L<Math::PlanePath::ComplexRevolving>

L<Math::PlanePath::Rows>,
L<Math::PlanePath::Columns>,
L<Math::PlanePath::Diagonals>,
L<Math::PlanePath::DiagonalsAlternating>,
L<Math::PlanePath::DiagonalsOctant>,
L<Math::PlanePath::Staircase>,
L<Math::PlanePath::StaircaseAlternating>,
L<Math::PlanePath::Corner>

L<Math::PlanePath::PyramidRows>,
L<Math::PlanePath::PyramidSides>,
L<Math::PlanePath::CellularRule>,
L<Math::PlanePath::CellularRule54>,
L<Math::PlanePath::CellularRule57>,
L<Math::PlanePath::CellularRule190>,
L<Math::PlanePath::UlamWarburton>,
L<Math::PlanePath::UlamWarburtonQuarter>

L<Math::PlanePath::DiagonalRationals>,
L<Math::PlanePath::FactorRationals>,
L<Math::PlanePath::GcdRationals>,
L<Math::PlanePath::RationalsTree>,
L<Math::PlanePath::FractionsTree>,
L<Math::PlanePath::ChanTree>,
L<Math::PlanePath::CfracDigits>,
L<Math::PlanePath::CoprimeColumns>,
L<Math::PlanePath::DivisibleColumns>,
L<Math::PlanePath::WythoffArray>,
L<Math::PlanePath::PowerArray>,
L<Math::PlanePath::File>

=for my_pod see_also end

L<Math::PlanePath::LCornerTree>,
L<Math::PlanePath::LCornerReplicate>,
L<Math::PlanePath::ToothpickTree>,
L<Math::PlanePath::ToothpickReplicate>,
L<Math::PlanePath::ToothpickUpist>,
L<Math::PlanePath::ToothpickSpiral>

L<Math::NumSeq::PlanePathCoord>,
L<Math::NumSeq::PlanePathDelta>,
L<Math::NumSeq::PlanePathTurn>,
L<Math::NumSeq::PlanePathN>

L<math-image>, displaying various sequences on these paths.

F<examples/numbers.pl> in the Math-PlanePath source code, to print all the
paths.

=head2 Other Ways To Do It

L<Math::Fractal::Curve>,
L<Math::Curve::Hilbert>,
L<Algorithm::SpatialIndex::Strategy::QuadTree>

PerlMagick (module L<Image::Magick>) demo scripts F<lsys.pl> and F<tree.pl>

=head1 HOME PAGE

http://user42.tuxfamily.org/math-planepath/index.html

http://user42.tuxfamily.org/math-planepath/gallery.html

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
