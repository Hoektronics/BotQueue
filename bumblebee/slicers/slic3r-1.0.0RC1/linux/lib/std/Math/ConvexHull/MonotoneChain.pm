package Math::ConvexHull::MonotoneChain;
use 5.008001;
use strict;
use warnings;

require Exporter;

our @ISA = qw(Exporter);
our @EXPORT_OK = qw(
  convex_hull
);
our @EXPORT = qw();
our %EXPORT_TAGS = ('all' => \@EXPORT_OK);

our $VERSION = '0.01';

require XSLoader;
XSLoader::load('Math::ConvexHull::MonotoneChain', $VERSION);

sub convex_hull {
  my $ary_ref = shift;
  return convex_hull_sorted([
    sort {$a->[0] <=> $b->[0] || $a->[1] <=> $b->[1]} @$ary_ref
  ]);
}
1;
__END__

=head1 NAME

Math::ConvexHull::MonotoneChain - Andrew's monotone chain algorithm for finding a convex hull in 2D

=head1 SYNOPSIS

  use Math::ConvexHull::MonotoneChain 'convex_hull';
  my $ch = convex_hull(
    [
      [0, 0],
      [0, 1],
      [1, 0],
      [0.5, 0.5],
      [1, 1],
    ]
  );
  
  # $ch is now:
  # [ [0, 0],
  #   [1, 0],
  #   [1, 1],
  #   [0, 1], ]

=head1 DESCRIPTION

This is somewhat experimental still.

This (XS) module optionally exports a single function C<convex_hull>
which calculates the convex hull of the input points and returns it.
The algorithm is C<O(n log n)> due to having to sort the input list,
but should be somewhat faster than a plain Graham's scan (also C<O(n log n)>)
in practice since it avoids polar coordinates.

=head1 FUNCTIONS

=head2 convex_hull

Expects an array ref of points as input, returns an array ref of 
of the points in the convex hull, ordered counter-clockwise.

I<point> refers to an array reference containing an X, and a Y coordinate.

For less than three input points, this will return an array reference
whose elements are the input points (without cloning).

=head1 SEE ALSO

L<Math::ConvexHull>, which uses Graham's scan in pure Perl.

=head1 AUTHOR

Steffen Mueller, E<lt>smueller@cpan.orgE<gt>

=head1 COPYRIGHT AND LICENSE

Copyright (C) 2011 by Steffen Mueller

This library is free software; you can redistribute it and/or modify
it under the same terms as Perl itself, either Perl version 5.8.0 or,
at your option, any later version of Perl 5 you may have available.

=cut

