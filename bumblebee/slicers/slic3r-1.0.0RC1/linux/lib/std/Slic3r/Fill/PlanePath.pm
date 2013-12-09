package Slic3r::Fill::PlanePath;
use Moo;

extends 'Slic3r::Fill::Base';

use Slic3r::Geometry qw(scale X1 Y1 X2 Y2);

sub multiplier () { 1 }

sub get_n {
    my $self = shift;
    my ($path, $bounding_box) = @_;
    
    my ($n_lo, $n_hi) = $path->rect_to_n_range(@$bounding_box);
    return ($n_lo .. $n_hi);
}

sub process_polyline {}

sub fill_surface {
    my $self = shift;
    my ($surface, %params) = @_;
    
    # rotate polygons
    my $expolygon = $surface->expolygon->clone;
    my $rotate_vector = $self->infill_direction($surface);
    $self->rotate_points($expolygon, $rotate_vector);
    
    my $distance_between_lines = scale $params{flow_spacing} / $params{density} * $self->multiplier;
    my $bounding_box = $expolygon->bounding_box;
    
    (ref $self) =~ /::([^:]+)$/;
    my $path = "Math::PlanePath::$1"->new;
    my @n = $self->get_n($path, [ map +($_ / $distance_between_lines), @{$bounding_box->bb} ]);
    
    my $polyline = Slic3r::Polyline->new(
        map [ map {$_*$distance_between_lines} $path->n_to_xy($_) ], @n,
    );
    return {} if @$polyline <= 1;
    
    $self->process_polyline($polyline, $bounding_box);
    
    my @paths = map $_->clip_with_expolygon($expolygon),
        $polyline->clip_with_polygon($bounding_box->polygon);
    
    if (0) {
        require "Slic3r/SVG.pm";
        Slic3r::SVG::output("fill.svg",
            polygons => $expolygon,
            polylines => [map $_->p, @paths],
        );
    }
    
    # paths must be rotated back
    $self->rotate_points_back(\@paths, $rotate_vector);
    
    return { flow_spacing => $params{flow_spacing} }, @paths;
}

1;
