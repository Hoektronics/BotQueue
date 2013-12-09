package Moo::_Utils;

no warnings 'once'; # guard against -w

sub _getglob { \*{$_[0]} }
sub _getstash { \%{"$_[0]::"} }

use constant lt_5_8_3 => ( $] < 5.008003 or $ENV{MOO_TEST_PRE_583} ) ? 1 : 0;
use constant can_haz_subname => eval { require Sub::Name };

use strictures 1;
use Module::Runtime qw(require_module);
use Devel::GlobalDestruction ();
use base qw(Exporter);
use Moo::_mro;

our @EXPORT = qw(
    _getglob _install_modifier _load_module _maybe_load_module
    _get_linear_isa _getstash _install_coderef _name_coderef
    _unimport_coderefs _in_global_destruction
);

sub _in_global_destruction ();
*_in_global_destruction = \&Devel::GlobalDestruction::in_global_destruction;

sub _install_modifier {
  my ($into, $type, $name, $code) = @_;

  if (my $to_modify = $into->can($name)) { # CMM will throw for us if not
    require Sub::Defer;
    Sub::Defer::undefer_sub($to_modify);
  }

  Class::Method::Modifiers::install_modifier(@_);
}

our %MAYBE_LOADED;

sub _load_module {
  (my $proto = $_[0]) =~ s/::/\//g;
  return 1 if $INC{"${proto}.pm"};
  # can't just ->can('can') because a sub-package Foo::Bar::Baz
  # creates a 'Baz::' key in Foo::Bar's symbol table
  my $stash = _getstash($_[0])||{};
  return 1 if grep +(!ref($_) and *$_{CODE}), values %$stash;
  require_module($_[0]);
  return 1;
}

sub _maybe_load_module {
  return $MAYBE_LOADED{$_[0]} if exists $MAYBE_LOADED{$_[0]};
  (my $proto = $_[0]) =~ s/::/\//g;
  local $@;
  if (eval { require "${proto}.pm"; 1 }) {
    $MAYBE_LOADED{$_[0]} = 1;
  } else {
    if (exists $INC{"${proto}.pm"}) {
      warn "$_[0] exists but failed to load with error: $@";
    }
    $MAYBE_LOADED{$_[0]} = 0;
  }
  return $MAYBE_LOADED{$_[0]};
}

sub _get_linear_isa {
  return mro::get_linear_isa($_[0]);
}

sub _install_coderef {
  no warnings 'redefine';
  *{_getglob($_[0])} = _name_coderef(@_);
}

sub _name_coderef {
  shift if @_ > 2; # three args is (target, name, sub)
  can_haz_subname ? Sub::Name::subname(@_) : $_[1];
}

sub _unimport_coderefs {
  my ($target, $info) = @_;
  return unless $info and my $exports = $info->{exports};
  my %rev = reverse %$exports;
  my $stash = _getstash($target);
  foreach my $name (keys %$exports) {
    if ($stash->{$name} and defined(&{$stash->{$name}})) {
      if ($rev{$target->can($name)}) {
        my $old = delete $stash->{$name};
        my $full_name = join('::',$target,$name);
        # Copy everything except the code slot back into place (e.g. $has)
        foreach my $type (qw(SCALAR HASH ARRAY IO)) {
          next unless defined(*{$old}{$type});
          no strict 'refs';
          *$full_name = *{$old}{$type};
        }
      }
    }
  }
}

sub STANDARD_DESTROY {
  my $self = shift;

  my $e = do {
    local $?;
    local $@;
    eval {
      $self->DEMOLISHALL(_in_global_destruction);
    };
    $@;
  };

  no warnings 'misc';
  die $e if $e; # rethrow
}

1;
