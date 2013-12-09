package Math::Geometry::Voronoi;

use 5.008;
use strict;
use warnings;

our $VERSION = '1.3';

require XSLoader;
XSLoader::load('Math::Geometry::Voronoi', $VERSION);

use Params::Validate qw(validate ARRAYREF CODEREF);
use List::Util qw(min max sum);

use base 'Class::Accessor::Fast';
__PACKAGE__->mk_accessors(
                         qw(points lines edges vertices xmin ymin xmax ymax));

sub new {
    my $pkg  = shift;
    my %args = validate(@_, {points => {type => ARRAYREF}});
    my $self = bless({points => $args{points}}, $pkg);

    $self->sort_points();

    return $self;
}

# C code needs points sorted by y then by x and needs min and max for
# both - should provide a way for the client to provide this
sub sort_points {
    my $self   = shift;
    my $points = $self->points();

    @$points =
      sort { $a->[1] <=> $b->[1] || $a->[0] <=> $b->[0] } @$points;

    $self->ymin($points->[0][1]);
    $self->ymax($points->[-1][1]);

    my @x = map { $_->[0] } @$points;
    $self->xmin(min(@x));
    $self->xmax(max(@x));

    return;
}

sub compute {
    my $self = shift;

    my $result = compute_voronoi_xs($self->points,
                                    $self->xmin,
                                    $self->xmax,
                                    $self->ymin,
                                    $self->ymax);

    $self->lines($result->{lines});
    $self->vertices($result->{vertices});
    $self->edges($result->{edges});

    return;
}

sub cmp_verts_ab { return cmp_verts($a,$b); }

# a low x value is placed before a high x value. if both x values 
# are the same, a high y value is placed before a low y value. 
sub cmp_verts {
    return ($_[0]->[0] <=> $_[1]->[0] || $_[1]->[1] <=> $_[0]->[1] );
}


sub vert_inside_bounds {
    my ($self, $x,$y) = @_;
    return (
        $x >= $self->xmin and $x <= $self->xmax and
        $y >= $self->ymin and $y <= $self->ymax
    );
}


sub boundry_interesection_verts {
    my($self, $a,$b,$c) = @_;
    my $verts = [];

    if($b){
        my $v1 = [$self->xmin,($c-$a*$self->xmin)/$b];
        my $v2 = [$self->xmax,($c-$a*$self->xmax)/$b];
        push ( @$verts, $v1 ) if ( $self->vert_inside_bounds( @$v1 ) );
        push ( @$verts, $v2 ) if ( $self->vert_inside_bounds( @$v2 ) );
    }

    if($a){
        my $v1 = [($c-$b*$self->ymax)/$a,$self->ymax];
        my $v2 = [($c-$b*$self->ymin)/$a,$self->ymin];
        push ( @$verts, $v1 ) if ( $self->vert_inside_bounds( @$v1 ) );
        push ( @$verts, $v2 ) if ( $self->vert_inside_bounds( @$v2 ) );
    }

    $verts;
}


sub polygons {
    my $self = shift;
    my %args = validate(@_,
                        {normalize_vertices => {type     => CODEREF,
                                                optional => 1
                                               },
                        });
    my $points   = $self->points;
    my $lines    = $self->lines;
    my $edges    = $self->edges;
    my $vertices = $self->vertices;

    if (my $norm = $args{normalize_vertices}) {
        $vertices = [map { [$norm->($_->[0]), $norm->($_->[1])] } @$vertices];
    }

    my @edges_by_point;
    EDGE: foreach my $edge (@$edges) {
        my ($l, $v1, $v2) = @$edge;
        next EDGE if( $v1 == -1 and $v2 == -1 );
        my ($lon1, $lat1, $lon2, $lat2);
        
        my $ivs = $self->boundry_interesection_verts(@{$lines->[$l]});
        $ivs = [sort cmp_verts_ab @$ivs];
        
        if( my $norm = $args{normalize_vertices}) {
            $ivs = [map { [$norm->($_->[0]), $norm->($_->[1])] } @$ivs];
        }
        
        ($lat1,$lon1) = @{$vertices->[$v1]} if( $v1 != -1 );
        ($lat2,$lon2) = @{$vertices->[$v2]} if( $v2 != -1 );

        if( $v1 == -1 ) {
            next EDGE unless( @$ivs and $lat2 +0 == $lat2 and $lon2 +0 == $lon2 );
            if( cmp_verts( [$lat2,$lon2], $ivs->[0] ) > 0 ) {
                ($lat1,$lon1) = @{$ivs->[0]};
            } elsif( cmp_verts( [$lat2,$lon2], $ivs->[1] ) > 0 ) {
                ($lat1,$lon1) = @{$ivs->[1]};
            } else {
                next EDGE;
            }
        }
        if( $v2 == -1 ) {
            next EDGE unless( @$ivs and $lat1 +0 == $lat1 and $lon1 +0 == $lon1 );
            if( cmp_verts( [$lat1,$lon1], $ivs->[1] ) < 0 ) {
                ($lat2,$lon2) = @{$ivs->[1]};
            } elsif( cmp_verts( [$lat1,$lon1], $ivs->[0] ) < 0 ) {
                ($lat2,$lon2) = @{$ivs->[0]};
            } else {
                next EDGE;
            }
        }
        
        # if any of the coords are NaN things break. 
        next EDGE if( grep {$_ +0 != $_ } ($lat1,$lon1,$lat2,$lon2));
        
        my ($p1, $p2) = ($lines->[$l][3], $lines->[$l][4]);

        if ($p1 != -1 and $p2 != -1) {
            foreach my $p ($p1, $p2) {
                push @{$edges_by_point[$p]}, [$lat1, $lon1, $lat2, $lon2];
            }
        }
    }

    my @polygons;
    foreach my $p (0 .. $#$points) {
        my $stack = $edges_by_point[$p];
        next unless $stack;
        # can't make a polygon with less than 2 edges
        next unless @$stack >= 2;

        my @poly = ();
        foreach my $this ( @$stack ) {
            if( 
                !grep { $_->[0] == $this->[0] && $_->[1] == $this->[1] } @poly
                and $this->[0] +0 == $this->[0] 
                and $this->[1] +0 == $this->[1]
            ) {
                push @poly, [$this->[0],$this->[1]];
            }
            if( 
                !grep { $_->[0] == $this->[2] && $_->[1] == $this->[3] } @poly
                and $this->[2] +0 == $this->[2] 
                and $this->[3] +0 == $this->[3]
            ) {
                push @poly, [$this->[2],$this->[3]];
            }
        }

        #TODO: if this point is the closest point to a corner... 
        # add that corner as a vert on this poly

        # sort poly's verts (anti?) clockwise around the point $points->[$p];
        @poly = sort {
            my($lat1,$lon1) = (
                $a->[0] - $points->[$p]->[0], 
                $a->[1] - $points->[$p]->[1]
            );

            my($lat2,$lon2) = (
                $b->[0] - $points->[$p]->[0], 
                $b->[1] - $points->[$p]->[1]
            );
            return atan2($lon1,$lat1) <=> atan2($lon2,$lat2);
        } @poly;

        # make a list of the first points
        push @polygons, [$p, map { [$_->[0], $_->[1]] } @poly];
    }

    return @polygons;
}

sub _dump_poly {
    my $poly = shift;
    return
      "[ \n\t" . join(", \n\t", map { "[$_->[0],$_->[1]]" } @$poly) . " ]\n";
}

1;
__END__

=head1 NAME

Math::Geometry::Voronoi - compute Voronoi diagrams from sets of points

=head1 SYNOPSIS

    use Math::Geometry::Voronoi;

    # load a set of points
    my @points = ([1,   2],
                  [1,   3],
                  [2,   2],
                  [0,   1],
                  [0,   10],
                  [0.5, 11]);
    my $geo = Math::Geometry::Voronoi->new(points => \@points);

    # compute your diagram
    $geo->compute;

    # extract features
    my $lines    = $geo->lines;
    my $edges    = $geo->edges;
    my $vertices = $geo->vertices;

    # build polygons
    my @polygons = $geo->polygons;

=head1 DESCRIPTION

This module computes Voronoi diagrams from a set of input points.
Info on Voronoi diagrams can be found here:

  http://en.wikipedia.org/wiki/Voronoi_diagram

This module is a wrapper around a C implementation found here:

  http://www.derekbradley.ca/voronoi.html

Which is itself a modification of code by Steve Fortune, the inventor
of the algorithm used (Fortune's algorithm):

  http://cm.bell-labs.com/who/sjf/

I made changes to the C code to allow reading input and writing output
to/from Perl data-structures.  I also modified the memory allocation
code to use Perl's memory allocator.  Finally, I changed all floats to
doubles to provide better precision and to match Perl's NVs.

=head1 INTERFACE

=head2 new

    my @points = ([1,   2],
                  [1,   3],
                  [2,   2],
                  [0,   1],
                  [0,   10],
                  [0.5, 11]);
    my $geo = Math::Geometry::Voronoi->new(points => \@points);

Create a new object, passing in a single required parameter called
'points'.  This must be an array or arrays containing at least two
values each, the X,Y values for your points.  Any extra data will be
ignored.

=head2 points

Returns the I<sorted> set of points used by the voronoi algorithm.
This is the ordering refered to by the lines() output below.

=head2 compute

Call this to build the diagram.  Returns nothing.

=head2 lines

Returns an array ref containing arrays of lines in the output diagram.
The data by index:

  0: the a value in the ax + by = c equation for the line
  1: the b value
  2: the c value
  3: the index of one point for which this line is the bisector.
  4: the index of the other point for which this line is the bisector.

Note that 3 and 4 are not the end-points of the line - they are points
perpendicular to the line.  Either 3 or 4 may be -1 meaning no point.

=head2 vertices

Returns an array ref containing arrays of vertices in the output
diagram.  These are the points which connect edges running along the
lines.  The data by index:

  0: the x value
  1: the y value

=head2 edges

Returns an array ref containing arrays of edges in the output diagram.
An edge is defined as a segment of a line running between two
vertices.  The data by index:

  0: the index of the line
  1: the index of vertex 1
  2: the index of vertex 2

Either 1 or 2 can be -1 meaning "infinite".

=head2 polygons

  @polys = $geo->polygons();

This method attempts to assemble polygons from non-infinite edges in
the diagram.  This part of the code is written in Perl and is of my
own invention.  I needed this facility in order to color the diagrams
created by this module.  It seems to work reasonably well for my uses
but I'm sure it's nowhere near the quality of Steve Fortune's code!
Feedback welcome.

This method returns a reference to an array containing first a point
index and then a list of vertex coordinates.  The point is the point
inside the polygon and the vertices are in drawing order for the
closed polygon surrounding the point.  For example:

  @polys = ( $point_index, [$lat1, $lon1], [$lat2, $lon2], ... );

One optional parameter is available - normalize_vertices.  This option
is necessary because the algorithm used needs to match up points from
one edge to another and doing that with floating point numbers
requires some kind of normalization (otherwise 1.1 != 1.10001).  For
example, if your coordinates are on an integer grid you might do:

  @polys = $geo->polygons(normalize_vertices => sub { int($_[0]) });

Or if you're using floating point and your coordinates are useful down
to 2 decimal places:

  @polys = $geo->polygons(normalize_vertices => sub { sprintf("%.2f", $_[0]) });

The point is to produce coordinates in a format where they will
compare as equal textually, side-stepping the problem of comparing
floats numerically.

=head1 TODO

Possible projects, if you're in the mood to help out:

  - Add the ability to combine polygons based on a mapping of
    same-type points.  Map overlays get cluttered by internal lines
    with you're coloring multiple polygons the same.  All edges
    connect exactly two polygons, so this should be relatively easy.
    Sadly, my limited math skills have thwarted me on this one - I
    spent several days but ultimately couldn't get it working reliably
    on all possible shapes.

  - Remove the need for the normalize_vertices option to polygons(),
    somehow (fuzzy matching?).

  - Setup a site where people can play with the module visually and
    see purty colors.  Could be an excuse to play with the new canvas
    stuff in modern browsers.

  - Add tests that actually examine the output for sanity. So far the
    tests just look at the format and range of the output data - to
    see if it's actually doing a decent diagram I look at graphical
    output.

=head1 AUTHOR

Sam Tregar <sam@tregar.com>

=head1 COPYRIGHT AND LICENSE

As far as I can tell the underlying C code used here never had a
license attached to it, or if it did I couldn't find any trace of it.
If this worries you please contact Steve and Derek through the links
above.

The Perl and XS code in this library is free software; you can
redistribute it and/or modify it under the same terms as Perl itself,
either Perl version 5.8.5 or, at your option, any later version of
Perl 5 you may have available.


=cut
