package Moo::HandleMoose::FakeMetaClass;

sub DESTROY { }

sub AUTOLOAD {
  my ($meth) = (our $AUTOLOAD =~ /([^:]+)$/);
  require Moo::HandleMoose;
  Moo::HandleMoose::inject_real_metaclass_for((shift)->{name})->$meth(@_)
}
sub can {
  require Moo::HandleMoose;
  Moo::HandleMoose::inject_real_metaclass_for((shift)->{name})->can(@_)
}
sub isa {
  require Moo::HandleMoose;
  Moo::HandleMoose::inject_real_metaclass_for((shift)->{name})->isa(@_)
}
sub make_immutable { $_[0] }

1;
