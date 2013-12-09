package Method::Generate::DemolishAll;

use strictures 1;
use base qw(Moo::Object);
use Sub::Quote;
use Moo::_Utils;
use B qw(perlstring);

sub generate_method {
  my ($self, $into) = @_;
  quote_sub "${into}::DEMOLISHALL", join '',
    $self->_handle_subdemolish($into),
    qq{    my \$self = shift;\n},
    $self->demolishall_body_for($into, '$self', '@_'),
    qq{    return \$self\n};
  quote_sub "${into}::DESTROY", join '',
    q!    my $self = shift;
    my $e = do {
      local $?;
      local $@;
      require Moo::_Utils;
      eval {
        $self->DEMOLISHALL(Moo::_Utils::_in_global_destruction);
      };
      $@;
    };
  
    no warnings 'misc';
    die $e if $e; # rethrow
  !;
}

sub demolishall_body_for {
  my ($self, $into, $me, $args) = @_;
  my @demolishers =
    grep *{_getglob($_)}{CODE},
    map "${_}::DEMOLISH",
    @{Moo::_Utils::_get_linear_isa($into)};
  join '', map qq{    ${me}->${_}(${args});\n}, @demolishers;
}

sub _handle_subdemolish {
  my ($self, $into) = @_;
  '    if (ref($_[0]) ne '.perlstring($into).') {'."\n".
  '      return shift->Moo::Object::DEMOLISHALL(@_)'.";\n".
  '    }'."\n";
}

1;
