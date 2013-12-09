package Slic3r::Build;
use strict;
use warnings;

use Cava::Packager;
$Slic3r::var = Cava::Packager::GetResourcePath;

1;
