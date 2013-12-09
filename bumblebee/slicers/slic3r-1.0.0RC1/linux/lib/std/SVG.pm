=pod 

=head1 NAME

SVG - Perl extension for generating Scalable Vector Graphics (SVG) documents

=cut

package SVG;

use strict;
use vars qw($VERSION @ISA $AUTOLOAD);
use Exporter;
use SVG::XML;
use SVG::Element;
use SVG::Extension;
use Scalar::Util qw/weaken/;
use warnings;

@ISA = qw(SVG::Element SVG::Extension);

$VERSION = "2.50";

#-------------------------------------------------------------------------------

=pod 

=head2 VERSION

Version 2.44, 08  April, 2008

Refer to L<SVG::Manual> for the complete manual

=head1 DESCRIPTION

SVG is a 100% Perl module which generates a nested data structure containing the
DOM representation of an SVG (Scalable Vector Graphics) image. Using SVG, you
can generate SVG objects, embed other SVG instances into it, access the DOM
object, create and access javascript, and generate SMIL animation content.

Refer to L<SVG::Manual> for the complete manual.

=head1 AUTHOR

Ronan Oger, RO IT Systemms GmbH, cpan@roitsystems.com.com

=head1 CREDITS

I would like to thank the following people for contributing to this module with
patches, testing, suggestions, and other nice tidbits:
Peter Wainwright, Ian Hickson, Adam Schneider, Steve Lihn, Allen Day 

=head1 EXAMPLES

http://www.roitsystems.com/index.shtml?svg.pod

=head1 SEE ALSO

perl(1),L<SVG>,L<SVG::DOM>,L<SVG::XML>,L<SVG::Element>,L<SVG::Parser>, L<SVG::Manual> L<SVG::Extension>
L<http://www.roitsystems.com/> ROASP.com: Serverside SVG server
L<http://www.roitsystems.com/> ROIT Systems: Commercial SVG perl solutions
L<http://www.w3c.org/Graphics/SVG/> SVG at the W3C

=cut


#-------------------------------------------------------------------------------

my %default_attrs = (
    # processing options
    -auto       => 0,       # permit arbitrary autoloads (only at import)
    -printerror => 1,       # print error messages to STDERR
    -raiseerror => 1,       # die on errors (implies -printerror)

    # rendering options
    -indent     => "\t",    # what to indent with
    -elsep      => "\n",    # element line (vertical) separator
    -nocredits  => 0,       # enable/disable credit note comment
    -namespace  => '',      # The root element's (and it's children's) namespace prefix

    # XML and Doctype declarations
    -inline     => 0,       # inline or stand alone
    -docroot    => 'svg',   # The document's root element
    -version    => '1.0',
    -extension  => '',
    -encoding   => 'UTF-8',
    -xml_svg    => 'http://www.w3.org/2000/svg',
    -xml_xlink  => 'http://www.w3.org/1999/xlink',
    -standalone => 'yes',
    -pubid      => "-//W3C//DTD SVG 1.0//EN", # formerly -identifier
    -sysid      => 'http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd',
);

sub import {
    my $package=shift;

    my $attr=undef;
    foreach (@_) {
        if ($attr) {
            $default_attrs{$attr}=$_;
            undef $attr;
        } elsif (exists $default_attrs{$_}) {
            $attr=$_;
        } else {
            /^-/ and die "Unknown attribute '$_' in import list\n";
            $SVG::Element::autosubs{$_}=1; # add to list of autoloadable tags
        }
    }

    # switch on AUTOLOADer, if asked.
    if ($default_attrs{'-auto'}) {
        *SVG::Element::AUTOLOAD=\&SVG::Element::autoload;
    }

    # predeclare any additional elements asked for by the user
    foreach my $sub (keys %SVG::Element::autosubs) {
        $SVG::Element::AUTOLOAD=("SVG::Element::$sub");
        SVG::Element::autoload();
    }

    delete $default_attrs{-auto}; # -auto is only allowed here, not in new

    return ();
}

#-------------------------------------------------------------------------------

=pod

=head1 Methods

SVG provides both explicit and generic element constructor methods. Explicit
generators are generally (with a few exceptions) named for the element they
generate. If a tag method is required for a tag containing hyphens, the method 
name replaces the hyphen with an underscore. ie: to generate tag <column-heading id="new">
you would use method $svg->column_heading(id=>'new').


All element constructors take a hash of element attributes and options;
element attributes such as 'id' or 'border' are passed by name, while options for the
method (such as the type of an element that supports multiple alternate forms)
are passed preceded by a hyphen, e.g '-type'. Both types may be freely
intermixed; see the L<"fe"> method and code examples througout the documentation
for more examples.

=head2 new (constructor)

$svg = SVG->new(%attributes)

Creates a new SVG object. Attributes of the document SVG element be passed as
an optional list of key value pairs. Additionally, SVG options (prefixed with
a hyphen) may be set on a per object basis:

B<Example:>

    my $svg1=new SVG;

    my $svg2=new SVG(id => 'document_element');

    my $svg3=new SVG(
        -printerror => 1,
        -raiseerror => 0,
        -indent     => '  ',
        -elsep      => "\n",  # element line (vertical) separator
        -docroot    => 'svg', # default document root element (SVG specification assumes svg). Defaults to 'svg' if undefined
        -xml_xlink  => 'http://www.w3.org/1999/xlink', # required by Mozilla's embedded SVG engine
        -sysid      => 'abc', # optional system identifier 
        -pubid      => "-//W3C//DTD SVG 1.0//EN", # public identifier default value is "-//W3C//DTD SVG 1.0//EN" if undefined
        -namespace  => 'mysvg',
        -inline     => 1
        id          => 'document_element',
        width       => 300,
        height      => 200,
    );

Default SVG options may also be set in the import list. See L<"EXPORTS"> above
for more on the available options. 

Furthermore, the following options:

    -version
    -encoding
    -standalone
    -namespace
    -inline
    -pubid (formerly -identifier)
    -sysid (standalone)

may also be set in xmlify, overriding any corresponding values set in the SVG->new declaration

=cut

#-------------------------------------------------------------------------------
#
# constructor for the SVG data model.
#
# the new constructor creates a new data object with a document tag at its base.
# this document tag then has either:
#     a child entry parent with its child svg generated (when -inline = 1)
# or
#     a child entry svg created.
#
# Because the new method returns the $self reference and not the 
# latest child to be created, a hash key -document with the reference to the hash
# entry of its already-created child. hence the document object has a -document reference
# to parent or svg if inline is 1 or 0, and parent will have a -document entry
# pointing to the svg child.
#
# This way, the next tag constructor will descend the
# tree until it finds no more tags with -document, and will add
# the next tag object there.
# refer to the SVG::tag method 

sub new ($;@) {
    my ($proto,%attrs) = @_;
    my $class = ref $proto || $proto;
    my $self;

    # establish defaults for unspecified attributes
    foreach my $attr (keys %default_attrs) {
        $attrs{$attr}=$default_attrs{$attr} unless exists $attrs{$attr}
    }
    $self = $class->SUPER::new('document');
    if (not $self->{-docref}) {
      $self->{-docref} = $self;
      weaken( $self->{-docref} );
    }
    unless ($attrs{-namespace}) {
        $attrs{'xmlns'} = $attrs{'xmlns'} || $attrs{'-xml_svg'};
    }
    $attrs{'xmlns:xlink'} = $attrs{'xmlns:xlink'} || $attrs{'-xml_xlink'} || 'http://www.w3.org/1999/xlink';
    $attrs{'xmlns:svg'} = $attrs{'xmlns:svg'} || $attrs{'-xml_svg'} || 'http://www.w3.org/2000/svg';


    $self->{-level} = 0;
    $self->{$_} = $attrs{$_} foreach keys %default_attrs;

    # create SVG object according to nostub attribute
    my $svg;
    unless ($attrs{-nostub}) {
        $svg = $self->svg(%attrs);
        $self->{-document} = $svg;
        weaken( $self->{-document} );
    }

    # add -attributes to SVG object
    #    $self->{-elrefs}->{$self}->{name} = 'document';
    #    $self->{-elrefs}->{$self}->{id} = '';

    return $self;
}

#-------------------------------------------------------------------------------

=pod

=head2 xmlify  (alias: to_xml render serialize serialise )

$string = $svg->xmlify(%attributes);

Returns xml representation of svg document.

B<XML Declaration>

    Name               Default Value
    -version           '1.0'               
    -encoding          'UTF-8'
    -standalone        'yes'
    -namespace         'svg' - namespace prefix for elements. 
                               Can also be used in any element method to over-ride
                               the current namespace prefix. Make sure to have
                               declared the prefix before using it.
    -inline            '0' - If '1', then this is an inline document.
    -pubid             '-//W3C//DTD SVG 1.0//EN';
    -sysid             'http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd'

=cut

sub xmlify ($;@) {

    my ($self,%attrs) = @_;
    my ($decl,$ns);

    my $credits = '';

    # Give the module and myself credit unless explicitly turned off
    unless ($self->{-docref}->{-nocredits}) {
        $self->comment("\n\tGenerated using the Perl SVG Module V$VERSION\n\tby Ronan Oger\n\tInfo: http://www.roitsystems.com/\n" );
    }

    foreach my $key (keys %attrs) {
        next unless ($key =~ /^\-/);
        $self->{$key} = $attrs{$key};
    }

    foreach my $key (keys %$self) {
        next unless ($key =~ /^\-/);
        $attrs{$key} ||= $self->{$key};
    }

    return $self->SUPER::xmlify($self->{-namespace});
}


*render=\&xmlify;
*to_xml=\&xmlify;
*serialise=\&xmlify;
*serialize=\&xmlify;


=head2 perlify ()

return the perl code which generates the SVG document as it currently exists.

=cut

sub perlify ($;@) {
    return shift->SUPER::perlify();
}

=head2 toperl ()

Alias for method perlify()

=cut

*toperl=\&perlify;

#-------------------------------------------------------------------------------

#LICENSE
#
#
#
#The modules in the SVG distribution are distributed under the same license
# as Perl itself. It is provided free of warranty and may be re-used freely.
#
#
#

1;
