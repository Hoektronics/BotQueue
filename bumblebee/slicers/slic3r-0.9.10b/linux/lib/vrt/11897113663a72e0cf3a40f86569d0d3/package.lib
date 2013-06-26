package File::Glob;

use strict;
our($VERSION, @ISA, @EXPORT_OK, @EXPORT_FAIL, %EXPORT_TAGS, $DEFAULT_FLAGS);

require XSLoader;
use feature 'switch';

@ISA = qw(Exporter);

# NOTE: The glob() export is only here for compatibility with 5.6.0.
# csh_glob() should not be used directly, unless you know what you're doing.

%EXPORT_TAGS = (
    'glob' => [ qw(
        GLOB_ABEND
	GLOB_ALPHASORT
        GLOB_ALTDIRFUNC
        GLOB_BRACE
        GLOB_CSH
        GLOB_ERR
        GLOB_ERROR
        GLOB_LIMIT
        GLOB_MARK
        GLOB_NOCASE
        GLOB_NOCHECK
        GLOB_NOMAGIC
        GLOB_NOSORT
        GLOB_NOSPACE
        GLOB_QUOTE
        GLOB_TILDE
        glob
        bsd_glob
    ) ],
);

@EXPORT_OK   = (@{$EXPORT_TAGS{'glob'}}, 'csh_glob');

$VERSION = '1.13';

sub import {
    require Exporter;
    local $Exporter::ExportLevel = $Exporter::ExportLevel + 1;
    Exporter::import(grep {
	my $passthrough;
	given ($_) {
	    $DEFAULT_FLAGS &= ~GLOB_NOCASE() when ':case';
	    $DEFAULT_FLAGS |= GLOB_NOCASE() when ':nocase';
	    when (':globally') {
		no warnings 'redefine';
		*CORE::GLOBAL::glob = \&File::Glob::csh_glob;
	    }
	    $passthrough = 1;
	}
	$passthrough;
    } @_);
}

XSLoader::load();

$DEFAULT_FLAGS = GLOB_CSH();
if ($^O =~ /^(?:MSWin32|VMS|os2|dos|riscos)$/) {
    $DEFAULT_FLAGS |= GLOB_NOCASE();
}

# File::Glob::glob() is deprecated because its prototype is different from
# CORE::glob() (use bsd_glob() instead)
sub glob {
    splice @_, 1; # don't pass PL_glob_index as flags!
    goto &bsd_glob;
}

## borrowed heavily from gsar's File::DosGlob
my %iter;
my %entries;

sub csh_glob {
    my $pat = shift;
    my $cxix = shift;
    my @pat;

    # glob without args defaults to $_
    $pat = $_ unless defined $pat;

    # extract patterns
    $pat =~ s/^\s+//;	# Protect against empty elements in
    $pat =~ s/\s+$//;	# things like < *.c> and <*.c >.
			# These alone shouldn't trigger ParseWords.
    if ($pat =~ /\s/) {
        # XXX this is needed for compatibility with the csh
	# implementation in Perl.  Need to support a flag
	# to disable this behavior.
	require Text::ParseWords;
	@pat = Text::ParseWords::parse_line('\s+',0,$pat);
    }

    # assume global context if not provided one
    $cxix = '_G_' unless defined $cxix;
    $iter{$cxix} = 0 unless exists $iter{$cxix};

    # if we're just beginning, do it all first
    if ($iter{$cxix} == 0) {
	if (@pat) {
	    $entries{$cxix} = [ map { doglob($_, $DEFAULT_FLAGS) } @pat ];
	}
	else {
	    $entries{$cxix} = [ doglob($pat, $DEFAULT_FLAGS) ];
	}
    }

    # chuck it all out, quick or slow
    if (wantarray) {
        delete $iter{$cxix};
        return @{delete $entries{$cxix}};
    }
    else {
        if ($iter{$cxix} = scalar @{$entries{$cxix}}) {
            return shift @{$entries{$cxix}};
        }
        else {
            # return undef for EOL
            delete $iter{$cxix};
            delete $entries{$cxix};
            return undef;
        }
    }
}

1;
__END__

