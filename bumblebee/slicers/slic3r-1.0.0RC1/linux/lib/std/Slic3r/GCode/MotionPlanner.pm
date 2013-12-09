package Slic3r::GCode::MotionPlanner;
use Moo;

has 'islands'       => (is => 'ro', required => 1);
has 'no_internal'   => (is => 'ro');
has 'last_crossings'=> (is => 'rw');
has '_inner'        => (is => 'rw', default => sub { [] });  # arrayref of arrayrefs of expolygons
has '_outer'        => (is => 'rw', default => sub { [] });  # arrayref of arrayrefs of polygons
has '_contours_ex'  => (is => 'rw', default => sub { [] });  # arrayref of arrayrefs of expolygons
has '_pointmap'     => (is => 'rw', default => sub { {} });  # { id => $point }
has '_edges'        => (is => 'rw', default => sub { {} });  # node_idx => { node_idx => distance, ... }
has '_crossing_edges' => (is => 'rw', default => sub { {} });  # edge_idx => bool
has '_tolerance'    => (is => 'lazy');

use List::Util qw(first);
use Slic3r::Geometry qw(A B scale epsilon);
use Slic3r::Geometry::Clipper qw(diff_ex offset);

# clearance (in mm) from the perimeters
has '_inner_margin' => (is => 'ro', default => sub { scale 0.5 });
has '_outer_margin' => (is => 'ro', default => sub { scale 2 });

# this factor weigths the crossing of a perimeter 
# vs. the alternative path. a value of 5 means that
# a perimeter will be crossed if the alternative path
# is >= 5x the length of the straight line we could
# follow if we decided to cross the perimeter.
# a nearly-infinite value for this will only permit
# perimeter crossing when there's no alternative path.
use constant CROSSING_FACTOR => 20;

use constant INFINITY => 'inf';

sub _build__tolerance { scale epsilon }

# setup our configuration space
sub BUILD {
    my $self = shift;
    
    my $edges = $self->_edges;
    my $crossing_edges = $self->_crossing_edges;
    
    # simplify islands
    @{$self->islands} = map $_->simplify($self->_inner_margin), @{$self->islands};
    
    # process individual islands
    for my $i (0 .. $#{$self->islands}) {        
        # offset the island inwards to make the boundaries for internal movements
        # so that no motion along external perimeters happens
        $self->_inner->[$i] = $self->no_internal
            ? []
            : $self->islands->[$i]->offset_ex(-$self->_inner_margin);
        
        # offset the island outwards to make the boundaries for external movements
        $self->_outer->[$i] = offset([ $self->islands->[$i]->contour ], $self->_outer_margin);
        
        # if internal motion is enabled, build a set of utility expolygons representing
        # the outer boundaries (as contours) and the inner boundaries (as holes). whenever
        # we jump from a hole to a contour or viceversa, we know we're crossing a perimeter
        if (!$self->no_internal) {
            $self->_contours_ex->[$i] = diff_ex(
                $self->_outer->[$i],
                [ map $_->contour, @{$self->_inner->[$i]} ],
            );
            
            # lines enclosed in inner expolygons are visible
            $self->_add_expolygon($_) for @{ $self->_inner->[$i] };
            
            # lines enclosed in expolygons covering perimeters are visible
            # (but discouraged)
            $self->_add_expolygon($_, 1) for @{ $self->_contours_ex->[$i] };
        }
    }
    
    {
        my @outer = (map @$_, @{$self->_outer});
        my @outer_ex = map Slic3r::ExPolygon->new($_), @outer;  # build ExPolygons for Boost
        
        # lines of outer polygons connect visible points
        for my $i (0 .. $#outer) {
            foreach my $line (@{$outer[$i]->lines}) {
                my $dist = $line->length;
                $edges->{$line->a}{$line->b} = $dist;
                $edges->{$line->b}{$line->a} = $dist;
            }
        }
        
        # lines connecting outer polygons are visible
        for my $i (0 .. $#outer) {
            for my $j (($i+1) .. $#outer) {
                for my $m (0 .. $#{$outer[$i]}) {
                    for my $n (0 .. $#{$outer[$j]}) {
                        my $line = Slic3r::Line->new($outer[$i][$m], $outer[$j][$n]);
                        if (!@{Boost::Geometry::Utils::multi_polygon_multi_linestring_intersection([ map $_->pp, @outer_ex ], [$line->pp])}) {
                            # this line does not cross any polygon
                            my $dist = $line->length;
                            $edges->{$outer[$i][$m]}{$outer[$j][$n]} = $dist;
                            $edges->{$outer[$j][$n]}{$outer[$i][$m]} = $dist;
                        }
                    }
                }
            }
        }
    }
    
    # lines connecting inner polygons contours are visible but discouraged
    if (!$self->no_internal) {
        my @inner = (map $_->contour, map @$_, @{$self->_inner});
        my @inner_ex = map Slic3r::ExPolygon->new($_), @inner;  # build ExPolygons for Boost
        for my $i (0 .. $#inner) {
            for my $j (($i+1) .. $#inner) {
                for my $m (0 .. $#{$inner[$i]}) {
                    for my $n (0 .. $#{$inner[$j]}) {
                        my $line = Slic3r::Line->new($inner[$i][$m], $inner[$j][$n]);
                        if (!@{Boost::Geometry::Utils::multi_polygon_multi_linestring_intersection([ map $_->pp, @inner_ex ], [$line->pp])}) {
                            # this line does not cross any polygon
                            my $dist = $line->length * CROSSING_FACTOR;
                            $edges->{$inner[$i][$m]}{$inner[$j][$n]} = $dist;
                            $edges->{$inner[$j][$n]}{$inner[$i][$m]} = $dist;
                            $crossing_edges->{$inner[$i][$m]}{$inner[$j][$n]} = 1;
                            $crossing_edges->{$inner[$j][$n]}{$inner[$i][$m]} = 1;
                        }
                    }
                }
            }
        }
    }
    
    $self->_pointmap({
        map +("$_" => $_), 
            (map @$_, map @$_, map @$_, @{$self->_inner}),
            (map @$_, map @$_, @{$self->_outer}),
            (map @$_, map @$_, map @$_, @{$self->_contours_ex}),
    });
    
    if (0) {
        my @lines = ();
        my %lines = ();
        for my $i (keys %{$self->_edges}) {
            for my $j (keys %{$self->_edges->{$i}}) {
                next if $lines{join '_', sort $i, $j};
                push @lines, [ map $self->_pointmap->{$_}, $i, $j ];
                $lines{join '_', sort $i, $j} = 1;
            }
        }
        
        require "Slic3r/SVG.pm";
        Slic3r::SVG::output("space.svg",
            lines           => \@lines,
            points          => [ values %{$self->_pointmap} ],
            no_arrows       => 1,
            expolygons      => $self->islands,
            #red_polygons    => [ map @{$_->holes}, map @$_, @{$self->_inner} ],
            #white_polygons    => [ map @$_, @{$self->_outer} ],
        );
        printf "%d islands\n", scalar @{$self->islands};
        
        eval "use Devel::Size";
        print  "MEMORY USAGE:\n";
        printf "  %-19s = %.1fMb\n", $_, Devel::Size::total_size($self->$_)/1024/1024
            for qw(_inner _outer _contours_ex _pointmap _edges _crossing_edges islands last_crossings);
        printf "  %-19s = %.1fMb\n", 'self', Devel::Size::total_size($self)/1024/1024;
    }
}

# given an expolygon, this subroutine connects all its visible points
sub _add_expolygon {
    my $self = shift;
    my ($expolygon, $crosses_perimeter) = @_;
    
    my $edges = $self->_edges;
    my $crossing_edges = $self->_crossing_edges;
    
    my @points = map @$_, @$expolygon;
    for my $i (0 .. $#points) {
        for my $j (($i+1) .. $#points) {
            my $line = Slic3r::Line->new($points[$i], $points[$j]);
            if ($expolygon->encloses_line($line, $self->_tolerance)) {
                my $dist = $line->length * ($crosses_perimeter ? CROSSING_FACTOR : 1);
                $edges->{$points[$i]}{$points[$j]} = $dist;
                $edges->{$points[$j]}{$points[$i]} = $dist;
                $crossing_edges->{$points[$i]}{$points[$j]} = 1;
                $crossing_edges->{$points[$j]}{$points[$i]} = 1;
            }
        }
    }
}

sub find_node {
    my $self = shift;
    my ($point, $near_to) = @_;
    
    # for optimal pathing, we should check visibility from $point to all $candidates, and then
    # choose the one that is nearest to $near_to among the visible ones; however this is probably too slow
    
    # if we're inside a hole, move to a point on hole;
    {
        my $polygon = first { $_->encloses_point($point) } (map @{$_->holes}, map @$_, @{$self->_inner});
        return $point->nearest_point([ @$polygon ]) if $polygon;
    }
    
    # if we're inside an expolygon move to a point on contour or holes
    {
        my $expolygon = first { $_->encloses_point_quick($point) } (map @$_, @{$self->_inner});
        return $point->nearest_point([ map @$_, @$expolygon ]) if $expolygon;
    }
    
    {
        my $outer_polygon_idx;
        if (!$self->no_internal) {
            # look for an outer expolygon whose contour contains our point
            $outer_polygon_idx = first { first { $_->contour->encloses_point($point) } @{$self->_contours_ex->[$_]} }
                0 .. $#{ $self->_contours_ex };
        } else {
            # # look for an outer expolygon containing our point
            $outer_polygon_idx = first { first { $_->encloses_point($point) } @{$self->_outer->[$_]} }
                0 .. $#{ $self->_outer };
        }
        my $candidates = defined $outer_polygon_idx
            ? [ map @{$_->contour}, @{$self->_inner->[$outer_polygon_idx]} ]
            : [ map @$_, map @$_, @{$self->_outer} ];
        $candidates = [ map @$_, @{$self->_outer->[$outer_polygon_idx]} ]
            if @$candidates == 0;
        return $point->nearest_point($candidates);
    }
}

sub shortest_path {
    my $self = shift;
    my ($from, $to) = @_;
    
    return Slic3r::Polyline->new($from, $to) if !@{$self->islands};
    
    # find nearest nodes
    my $new_from = $self->find_node($from, $to);
    my $new_to = $self->find_node($to, $from);
    
    my $root = "$new_from";
    my $target = "$new_to";
    my $edges = $self->_edges;
    my %dist = map { $_ => INFINITY } keys %$edges;
    $dist{$root} = 0;
    my %prev = map { $_ => undef } keys %$edges;
    my @unsolved = keys %$edges;
    my %crossings = ();  # node_idx => bool
    
    while (@unsolved) {
        # sort unsolved by distance from root
        # using a sorting option that accounts for infinity
        @unsolved = sort {
            $dist{$a} eq INFINITY ? +1 :
            $dist{$b} eq INFINITY ? -1 :
                $dist{$a} <=> $dist{$b};
        } @unsolved;
        
        # we'll solve the closest node
        last if $dist{$unsolved[0]} eq INFINITY;
        my $n = shift @unsolved;
        
        # stop search
        last if $n eq $target;
        
        # now, look at all the nodes connected to n
        foreach my $n2 (keys %{$edges->{$n}}) {
            # .. and find out if any of their estimated distances
	        # can be improved if we go through n
	        if ( ($dist{$n2} eq INFINITY) || ($dist{$n2} > ($dist{$n} + $edges->{$n}{$n2})) ) {
	            $dist{$n2} = $dist{$n} + $edges->{$n}{$n2};
	            $prev{$n2} = $n;
	            $crossings{$n} = 1 if $self->_crossing_edges->{$n}{$n2};
	        }
        }
    }
    
    my @points = ();
    my $crossings = 0;
    {
        my $pointmap = $self->_pointmap;
        my $u = $target;
        while (defined $prev{$u}) {
            unshift @points, $pointmap->{$u};
            $crossings++ if $crossings{$u};
            $u = $prev{$u};
        }
    }
    $self->last_crossings($crossings);
    return Slic3r::Polyline->new($from, $new_from, @points, $to); # @points already includes $new_to
}

1;
