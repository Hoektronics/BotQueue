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


# 
# bit_join_lowtohigh


package Math::PlanePath::Base::Digits;
use 5.004;
use strict;

use vars '$VERSION','@ISA','@EXPORT_OK';
$VERSION = 111;

use Exporter;
@ISA = ('Exporter');
@EXPORT_OK = ('parameter_info_array',
              'bit_split_lowtohigh',
              'digit_split_lowtohigh',
              'digit_join_lowtohigh',
              'round_down_pow');

# uncomment this to run the ### lines
# use Smart::Comments;


use constant parameter_info_radix2 => { name      => 'radix',
                                        share_key => 'radix_2',
                                        display   => 'Radix',
                                        type      => 'integer',
                                        minimum   => 2,
                                        default   => 2,
                                        width     => 3,
                                        description => 'Radix (number base).',
                                      };
use constant parameter_info_array => [ parameter_info_radix2() ];


#------------------------------------------------------------------------------

# ENHANCE-ME: Occasionally the $pow value is not wanted,
# eg. SierpinskiArrowhead, though that tends to be approximation code rather
# than exact range calculations etc.
#
sub round_down_pow {
  my ($n, $base) = @_;
  ### round_down_pow(): "$n base $base"

  # only for integer bases
  ### assert: $base == int($base)

  if ($n < $base) {
    return (1, 0);
  }

  # Math::BigInt and Math::BigRat overloaded log() return NaN, use integer
  # based blog()
  if (ref $n) {
    if ($n->isa('Math::BigRat')) {
      $n = int($n);
    }
    if ($n->isa('Math::BigInt')) {
      ### use blog() ...
      my $exp = $n->copy->blog($base);
      ### exp: "$exp"
      return (Math::BigInt->new(1)->blsft($exp,$base),
              $exp);
    }
  }

  my $exp = int(log($n)/log($base));
  my $pow = $base**$exp;
  ### n:   ref($n)."  $n"
  ### exp: ref($exp)."  $exp"
  ### pow: ref($pow)."  $pow"

  # check how $pow actually falls against $n, not sure should trust float
  # rounding in log()/log($base)
  # Crib: $n as first arg in case $n==BigFloat and $pow==BigInt
  if ($n < $pow) {
    ### hmm, int(log) too big, decrease...
    $exp -= 1;
    $pow = $base**$exp;
  } elsif ($n >= $base*$pow) {
    ### hmm, int(log) too small, increase...
    $exp += 1;
    $pow *= $base;
  }
  return ($pow, $exp);
}

#------------------------------------------------------------------------------
{
  my %binary_to_base4 = ('00' => '0',
                         '01' => '1',
                         '10' => '2',
                         '11' => '3');
  my @bigint_coderef;
  $bigint_coderef[4] = sub {
    (my $str = $_[0]->as_bin) =~ s/^0b//; # strip leading 0b
    if (length($str) & 1) {
      $str = "0$str";
    }
    $str =~ s/(..)/$binary_to_base4{$1}/ge;
    return reverse split //, $str;
  };
  $bigint_coderef[8] = sub {
    (my $str = $_[0]->as_oct) =~ s/^0//;  # strip leading 0
    return reverse split //, $str;
  };
  $bigint_coderef[10] = sub {
    return reverse split //, $_[0]->bstr;
  };
  $bigint_coderef[16] = sub {
    (my $str = $_[0]->as_hex) =~ s/^0x//;  # strip leading 0x
    return reverse map {hex} split //, $str;
  };

  # In _divrem() and _digit_split_lowtohigh() divide using rem=n%d then
  # q=(n-rem)/d so that quotient is an exact division.  If it's not exact
  # then goes to float and loses precision if UV=64bit NV=53bit.

  sub digit_split_lowtohigh {
    my ($n, $radix) = @_;
    ### _digit_split_lowtohigh(): $n

    $n || return; # don't return '0' from BigInt stringize
    if ($radix == 2) {
      return bit_split_lowtohigh($n);
    }

    my @ret;
    if (ref $n && $n->isa('Math::BigInt')) {
      if (my $coderef = $bigint_coderef[$radix]) {
        return $coderef->($_[0]);
      }
      $n = $n->copy; # for bdiv() modification
      do {
        (undef, my $digit) = $n->bdiv($radix);
        push @ret, $digit;
      } while ($n);
      if ($radix < 1_000_000) {  # plain scalars if fit
        foreach (@ret) {
          $_ = $_->numify;  # mutate array
        }
      }

    } else {
      do {
        my $digit = $n % $radix;
        push @ret, $digit;
        $n = int(($n - $digit) / $radix);
      } while ($n > 0);
    }

    return @ret;   # array[0] low digit
  }
}

use constant 1.02 _UV_MAX_PLUS_1 => ((~0 >> 1) + 1) * 2.0;

sub bit_split_lowtohigh {
  my ($n) = @_;
  my @ret;
  if ($n >= 1) {
    if (ref $n && $n->isa('Math::BigInt')) {
      (my $str = $n->as_bin) =~ s/^0b//;  # strip leading 0b
      return reverse split //, $str;
    }
    if ($n <= _UV_MAX_PLUS_1) {
      return reverse split //, sprintf('%b',$n);
    }
    do {
      my $digit = $n % 2;
      push @ret, $digit;
      $n = int(($n - $digit) / 2);
    } while ($n);
  }
  return @ret;   # array[0] low digit
}


#------------------------------------------------------------------------------
# $aref->[0] low digit
# ENHANCE-ME: BigInt new(), from_bin(), from_oct(), from_hex()

sub digit_join_lowtohigh {
  my ($aref, $radix, $zero) = @_;

  ### digit_join_lowtohigh() ...
  ### $aref
  ### $radix
  ### $zero

  my $n = (defined $zero ? $zero : 0);
  foreach my $digit (reverse @$aref) { # high to low
    ### $n
    $n *= $radix;
    $n += $digit;
  }
  ### $n
  return $n;
}


1;
__END__

=for stopwords Ryde Math-PlanePath lowtohigh Subclassing arrayref PlanePath hashref radix initializer

=head1 NAME

Math::PlanePath::Base::Digits -- helpers for digit based paths

=for test_synopsis my $n

=head1 SYNOPSIS

 use Math::PlanePath::Base::Digits 'digit_split_lowtohigh';
 foreach my $digit (digit_split_lowtohigh ($n, 16)) {
 }

=head1 DESCRIPTION

This is a few generic helper functions for paths based on digits or
powering.

They're designed to work on plain Perl integers and floats and there's some
special case support for C<Math::BigInt>.

=head1 EXPORTS

Nothing is exported by default but each function below can be as in the
usual L<Exporter> style,

    use Math::PlanePath::Base::Digits 'round_down_pow';

(But not C<parameter_info_radix2()>, for the reason described below.)

=head1 FUNCTIONS

=head2 Generic

=over 4

=item C<($power, $exponent) = round_down_pow ($n, $radix)>

Return the power of C<$radix> equal to or less than C<$n>.  For example

   ($pow, $exp) = round_down_pow (260, 2);
   # $pow==256  # the next lower power
   # $exp==8    # the exponent in that power
   # 2**8=256 is next below 260

=item C<@digits = digit_split_lowtohigh ($n, $radix)>

=item C<@bits = bit_split_lowtohigh ($n)>

Return a list of digits from C<$n> in base C<$radix>, or in binary.  For
example,

   @digits = digit_split_lowtohigh (12345, 10);
   # @digits = (5,4,3,2,1)   # decimal digits low to high

If C<$n==0> then the return is an empty list.  The current code expects C<$n
E<gt>= 0>.

"lowtohigh" in the name tries to make it clear which way the digits are
returned.  C<reverse()> can be used to get high to low instead (see
L<perlfunc/reverse>).

C<bit_split_lowtohigh()> is the same as C<digit_split_lowtohigh()> called
with radix=2.

=item C<$n = digit_join_lowtohigh ($arrayref, $radix)>

=item C<$n = digit_join_lowtohigh ($arrayref, $radix, $zero)>

Return a value made by joining digits from C<$arrayref> in base C<$radix>.
For example,

   @digits = (5,4,3,2,1)   # decimal digits low to high
   $n = digit_split_lowtohigh (\@digits, 10);
   # $n == 12345

Optional C<$zero> can be a 0 of an overloaded number type such as
C<Math::BigInt> to give a returned C<$n> of that type.

=back

=head2 Subclassing

=over

=item C<$aref = parameter_info_array()>

Return an arrayref of a C<radix> parameter, default 2.  This is designed to
be imported into a PlanePath subclass as its C<parameter_info_array()>
method.

    package Math::PlanePath::MySubclass;
    use Math::PlanePath::Base::Digits 'parameter_info_array';

The arrayref is

    [ { name      => 'radix',
        share_key => 'radix_2',
        display   => 'Radix',
        type      => 'integer',
        minimum   => 2,
        default   => 2,
        width     => 3,
        description => 'Radix (number base).',
      }
    ]

=item C<$href = Math::PlanePath::Base::Digits::parameter_info_radix2()>

Return the single C<radix> parameter hashref from the info above.  This can
be used when a subclass wants the radix parameter and other parameters too,

    package Math::PlanePath::MySubclass;
    use constant parameter_info_array =>
      [
       { name            => 'something_else',
         type            => 'integer',
         default         => '123',
       },
       Math::PlanePath::Base::Digits::parameter_info_radix2(),
      ];

If the "description" part should be more specific or more detailed then it
could be overridden with for example

   { %{Math::PlanePath::Base::Digits::parameter_info_radix2()},
     description => 'Radix, for both something and something.',
   },

This function is not exportable since it's meant for a one-off call in an
initializer and so no need to import it for repeated use.

=back

=head1 SEE ALSO

L<Math::PlanePath>,
L<Math::PlanePath::Base::Generic>

L<Math::BigInt>

=head1 HOME PAGE

http://user42.tuxfamily.org/math-planepath/index.html

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
