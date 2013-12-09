package Moo::_mro;

if ($] >= 5.010) {
  require mro;
} else {
  require MRO::Compat;
}

1;
