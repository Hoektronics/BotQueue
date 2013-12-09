package Boost::Geometry::Utils;
{
  $Boost::Geometry::Utils::VERSION = '0.15';
}
# ABSTRACT: Bindings for the Boost Geometry library
use strict;
use warnings;

require Exporter;
our @ISA = qw(Exporter);

use XSLoader;
XSLoader::load('Boost::Geometry::Utils', $Boost::Geometry::Utils::VERSION);

our @EXPORT_OK = qw(polygon_to_wkt linestring_to_wkt wkt_to_multilinestring
    polygon linestring polygon_linestring_intersection
    polygon_multi_linestring_intersection multi_polygon_multi_linestring_intersection
    point_within_polygon point_covered_by_polygon
    point_within_multi_polygon point_covered_by_multi_polygon
    linestring_simplify
    multi_linestring_simplify linestring_length polygon_centroid
    linestring_centroid multi_linestring_centroid multi_polygon
    correct_polygon correct_multi_polygon multi_linestring_multi_polygon_difference
    polygon_medial_axis polygon_area);

sub polygon_to_wkt {
    sprintf 'POLYGON(%s)', join ',', map { sprintf '(%s)', join ',', map { join ' ', @$_ } @$_ } @_;
}

sub linestring_to_wkt {
    sprintf "MULTILINESTRING(%s)", join ',', map { sprintf '(%s)', join ',', map { join ' ', @$_ } @$_ } @_;
}

sub wkt_to_multilinestring {
    return [] if $_[0] eq 'MULTILINESTRING()';
    $_[0] =~ s/^MULTILINESTRING\(\(//;
    $_[0] =~ s/\)\)$//;
    [ map [ map [ split / / ], split /,/ ], split /\),\(/, $_[0] ];
}

sub polygon {
    _polygon(\@_);
}

sub multi_polygon {
    _multi_polygon(\@_);
}

sub linestring {
    _multi_linestring(\@_)
}

sub multi_linestring {
    _multi_linestring(\@_)
}

1;


__END__
=pod

=head1 NAME

Boost::Geometry::Utils - Bindings for the Boost Geometry library

=head1 VERSION

version 0.07

=head1 SYNOPSIS

    use Boost::Geometry::Utils qw(polygon linestring polygon_linestring_intersection);
    
    my $square = [  # ccw
        [10, 10],
        [20, 10],
        [20, 20],
        [10, 20],
    ];
    my $hole_in_square = [  # cw
        [14, 14],
        [14, 16],
        [16, 16],
        [16, 14],
    ];
    my $polygon = polygon($square, $hole_in_square);
    my $linestring = linestring([ [5, 15], [30, 15] ]);
    
    my $intersection = polygon_linestring_intersection($polygon, $linestring);
    
    # $intersection is:
    # [
    #     [ [10, 15], [14, 15] ],
    #     [ [16, 15], [20, 15] ],
    # ]

=head1 ABSTRACT

This module provides bindings to perform some geometric operations using
the Boost Geometry library. It does not aim at providing full bindings
for such library, and that's why I left the I<Boost::Geometry> namespace
free. I'm unsure about the optimal architectural for providing full 
bindings, but I'm interested in such a project -- so, if you have ideas
please get in touch with me.

B<Warning:> the API could change in the future.

=head1 METHODS

=head2 polygon_linestring_intersection

Performs an intersection between the supplied polygon and linestring,
and returns an arrayref of linestrings (represented as arrayrefs of
points).
Note that such an intersection is also called I<clipping>.

=head2 polygon_multi_linestring_intersection

Same as I<polygon_linestring_intersection> but it accepts a multilinestring
object to perform multiple clippings in a single batch.

=head2 multi_polygon_multi_linestring_intersection

Same as I<polygon_multi_linestring_intersection> but it accepts a multipolygon
object to perform multiple clippings in a single batch.

=head2 multi_linestring_multi_polygon_difference

Performs a difference between the supplied multilinestring and the supplied
multipolygon. It returns a multilinestring object.

=head2 polygon_to_wkt

Converts one or more arrayref(s) of points to a WKT representation of
a polygon (with holes).

=head2 linestring_to_wkt

Converts an arrayref of points to a WKT representation of a multilinestring.

=head2 wkt_to_multilinestring

Parses a MULTILINESTRING back to a Perl data structure.

=head2 linestring_simplify

Accepts an arrayref of points representing a linestring and a numeric tolerance 
and returns an arrayref of points representing the simplified linestring.

=head2 multi_linestring_simplify

Accepts an arrayref of arrayrefs of points representing a multilinestring and a 
numeric tolerance and returns an arrayref of arrayrefs of points representing 
the simplified linestrings.

=head2 point_covered_by_polygon

Accepts a point and an arrayref of points representing a polygon and returns true 
or false according to the 'cover_by' strategy.

=head2 point_covered_by_multi_polygon

Same as above but accepts a multipolygon arrayref.

=head2 point_within_polygon

Accepts a point and an arrayref of points representing a polygon and returns true 
or false according to the 'within' strategy.

=head2 point_within_multi_polygon

Same as above but accepts a multipolygon arrayref.

=head2 linestring_length

Returns length of a linestring.

=head2 polygon_centroid

Returns the centroid point of a given polygon.

=head2 linestring_centroid

Returns the centroid point of a given linestring.

=head2 multi_linestring_centroid

Returns the centroid point of a given multi_linestring.

=head2 correct_polygon

Corrects the orientation(s) of the given polygon.

=head2 correct_multi_polygon

Corrects the orientation(s) of the given multi_polygon.

=head2 polygon_area

Returns the area of the given polygon.

=for Pod::Coverage linestring multi_linestring multi_polygon polygon

=head1 ACKNOWLEDGEMENTS

Thanks to mauke and mst (Matt S. Trout (cpan:MSTROUT) <mst@shadowcat.co.uk>)
for their valuable help in getting this to compile under Windows (MinGW) too.
Thanks to Mark Hindness for his work on data types conversion.

=head1 AUTHOR

Alessandro Ranellucci <aar@cpan.org>

=head1 COPYRIGHT AND LICENSE

This software is copyright (c) 2013 by Alessandro Ranellucci.

This is free software; you can redistribute it and/or modify it under
the same terms as the Perl 5 programming language system itself.

=cut

