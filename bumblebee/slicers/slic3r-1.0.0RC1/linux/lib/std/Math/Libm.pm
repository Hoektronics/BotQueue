package Math::Libm;

use strict;
# use warnings;
use Carp;
use vars qw(@ISA %EXPORT_TAGS @EXPORT_OK @EXPORT $VERSION $AUTOLOAD);

require Exporter;
require DynaLoader;
use AutoLoader;

@ISA = qw(Exporter DynaLoader);

# Items to export into callers namespace by default. Note: do not export
# names by default without a very good reason. Use EXPORT_OK instead.
# Do not simply export all your public functions/methods/constants.

# This allows declaration	use Math::Libm ':all';
# If you do not need this, moving things directly into @EXPORT or @EXPORT_OK
# will save memory.
%EXPORT_TAGS = ( 'all' => [ qw(
	M_1_PI
	M_2_PI
	M_2_SQRTPI
	M_E
	M_LN10
	M_LN2
	M_LOG10E
	M_LOG2E
	M_PI
	M_PI_2
	M_PI_4
	M_SQRT1_2
	M_SQRT2
	acos
	acosh
	asin
	asinh
	atan
	atanh
	cbrt
	ceil
	cosh
	erf
	erfc
	expm1
	floor
	hypot
	j0
	j1
	jn
	lgamma_r
	log10
	log1p
	pow
	rint
	sinh
	tan
	tanh
	y0
	y1
	yn
) ] );

@EXPORT_OK = ( @{ $EXPORT_TAGS{'all'} } );

@EXPORT = qw();

$VERSION = '1.00';

sub AUTOLOAD {
    # This AUTOLOAD is used to 'autoload' constants from the constant()
    # XS function.  If a constant is not found then control is passed
    # to the AUTOLOAD in AutoLoader.

    my $constname;
    ($constname = $AUTOLOAD) =~ s/.*:://;
    croak "& not defined" if $constname eq 'constant';
    my $val = constant($constname, @_ ? $_[0] : 0);
    if ($! != 0) {
	if ($! =~ /Invalid/ || $!{EINVAL}) {
	    $AutoLoader::AUTOLOAD = $AUTOLOAD;
	    goto &AutoLoader::AUTOLOAD;
	}
	else {
	    croak "Your vendor has not defined Math::Libm macro $constname";
	}
    }
    {
	no strict 'refs';
	# Fixed between 5.005_53 and 5.005_61
	if ($] >= 5.00561) {
#	    *$AUTOLOAD = sub () { $val };
	    *$AUTOLOAD = sub { $val };
	}
	else {
	    *$AUTOLOAD = sub { $val };
	}
    }
    goto &$AUTOLOAD;
}

bootstrap Math::Libm $VERSION;

# Preloaded methods go here.

# Autoload methods go after =cut, and are processed by the autosplit program.

1;
__END__
# Below is stub documentation for your module. You better edit it!

=head1 NAME

Math::Libm - Perl extension for the C math library, libm

=head1 SYNOPSIS

  use Math::Libm ':all';

  print "e = ", M_E, "\n";
  print "pi/2 = ", M_PI_2, "\n";
  print "erf(1) = ", erf(1), "\n";
  print "hypot(3,4) = ", hypot(3,4), "\n";

  my $signgam = 0;
  my $y = lgamma_r(-0.5, $signgam);
  print "signgam=$signgam lgamma=$y\n";

=head1 DESCRIPTION

This module is a translation of the C F<math.h> file.
It exports the following selected constants and functions.

=head2 EXPORT

None by default.

=head2 Exportable constants

  M_1_PI
  M_2_PI
  M_2_SQRTPI
  M_E
  M_LN10
  M_LN2
  M_LOG10E
  M_LOG2E
  M_PI
  M_PI_2
  M_PI_4
  M_SQRT1_2
  M_SQRT2

=head2 Exportable functions

  double acos(double x)
  double acosh(double x)
  double asin(double x)
  double asinh(double x)
  double atan(double x)
  double atanh(double x)
  double cbrt(double x)
  double ceil(double x)
  double cosh(double x)
  double erf(double x)
  double erfc(double x)
  double expm1(double x)
  double floor(double x)
  double hypot(double x, double y)
  double j0(double x)
  double j1(double x)
  double jn(int n, double x)
  double lgamma_r(double x, int signgam)
  double log10(double x)
  double log1p(double x)
  double pow(double x, double y)
  double rint(double x)
  double sinh(double x)
  double tan(double x)
  double tanh(double x)
  double y0(double x)
  double y1(double x)
  double yn(int n, double x)

=head1 AUTHOR

Daniel S. Lewart, E<lt>d-lewart@uiuc.eduE<gt>

=head1 SEE ALSO

L<perlfunc>, L<Math::Complex>, L<POSIX>.

=head1 BUGS

Only tested on AIX 4.2, FreeBSD 4.0, Linux 2.2.16, and Solaris 2.5.1.
May need some more functions.

=cut
