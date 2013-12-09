# Net::SSLeay.pm - Perl module for using Eric Young's implementation of SSL
#
# Copyright (c) 1996-2003 Sampo Kellomaki <sampo@iki.fi>, All Rights Reserved.
# Copyright (C) 2005 Florian Ragwitz <rafl@debian.org>, All Rights Reserved.
# Copyright (C) 2005 Mike McCauley <mikem@open.com.au>, All Rights Reserved.
#
# $Id$
#
# Version 1.04, 31.3.1999
# 30.7.1999, Tracking OpenSSL-0.9.3a changes, --Sampo
# 31.7.1999, version 1.05 --Sampo
# 7.4.2001,  fixed input error upon 0, OpenSSL-0.9.6a, version 1.06 --Sampo
# 18.4.2001, added TLSv1 support by Stephen C. Koehler
#            <koehler@securecomputing.com>, version 1.07, --Sampo
# 25.4.2001, 64 bit fixes by Marko Asplund <aspa@kronodoc.fi> --Sampo
# 17.4.2001, more error codes from aspa --Sampo
# 25.9.2001, added heaps and piles of newer OpenSSL auxiliary functions --Sampo
# 6.11.2001, got rid of $p_errs madness --Sampo
# 9.11.2001, added EGD (entropy gathering daemon) reference info --Sampo
# 7.12.2001, Added proxy support by Bruno De Wolf <bruno.dewolf@@pandora._be>
# 6.1.2002,  cosmetic fix to socket options from Kwindla Hultman Kramer <kwindla@@allafrica_.com>
# 25.3.2002, added post_https_cert and friends per patch from
#            mock@@obscurity.ogr, --Sampo
# 3.4.2002,  added `use bytes' from Marcus Taylor <marcus@@semantico_.com>
#            This avoids unicode/utf8 (as may appear in some XML docs)
#            from fooling the length comuptations. Dropped support for
#            perl5.005_03 because I do not have opportunity to test it. --Sampo
# 5.4.2002,  improved Unicode gotcha eliminator to support old perls --Sampo
# 8.4.2002,  added a small line end fix from Petr Dousa (pdousa@@kerio_.com)
# 17.5.2002, Added BIO_s_mem, BIO_new, BIO_free, BIO_write, BIO_read 
#            BIO_eof, BIO_pending, BIO_wpending, RSA_generate_key, RSA_free
#            --mikem@open._com.au
# 10.8.2002, Added SSL_peek patch to ssl_read_until from 
#            Peter Behroozi <peter@@fhpwireless_.com> --Sampo
# 21.8.2002, Added SESSION_get_master_key, SSL_get_client_random, SSL_get_server_random
#            --mikem@open.com_.au
# 2.9.2002,  Added SSL_CTX_get_cert_store, X509_STORE_add_cert, X509_STORE_add_crl
#            X509_STORE_set_flags, X509_load_cert_file, X509_load_crl_file
#            X509_load_cert_crl_file, PEM_read_bio_X509_CRL,
#            constants for X509_V_FLAG_* in order to support certificate revocation lists.
#            --mikem@open.com_.au
# 6.9.2002,  fixed X509_STORE_set_flags to X509_STORE_CTX_set_flags, --Sampo
# 19.9.2002, applied patch from Tim Engler <tim@burntcouch_.com>
# 18.2.2003, applied patch from Toni Andjelkovic <toni@soth._at>
# 13.6.2003, partially applied leak patch by Marian Jancar <mjancar@suse._cz>
# 25.6.2003, write_partial() return value patch from 
#            Kim Minh Kaplan <kmkaplan@selfoffice._com>
# 17.8.2003, added http support :-) --Sampo
# 17.8.2003, started 1.25 dev --Sampo
# 30.11.2005, Applied a patch by Peter Behroozi that adds get1_session() for session caching --Florian
# 30.11.2005, Applied a patch by ex8k-hbn@asahi-net.or.jp that limits the chunk size for tcp_read_all --Florian
# 30.11.2005, Applied a patch by ivan-cpan-rt@420.am that avoids adding a Host header if an own is specified in do_httpx3
# 13.12.2005, Added comments re thread safety and resetting of default_passwd_callback after use 
#             --mikem@open.com.au
#
# The distribution and use of this module are subject to the conditions
# listed in LICENSE file at the root of OpenSSL-0.9.7b
# distribution (i.e. free, but mandatory attribution and NO WARRANTY).

package Net::SSLeay;

use strict;
use Carp;
use vars qw($VERSION @ISA @EXPORT @EXPORT_OK $AUTOLOAD $CRLF);
use Socket;
use Errno;

require Exporter;
use AutoLoader;

# 0=no warns, 1=only errors, 2=ciphers, 3=progress, 4=dump data
$Net::SSLeay::trace = 0;  # Do not change here, use
                          # $Net::SSLeay::trace = [1-4]  in caller

# 2 = insist on v2 SSL protocol
# 3 = insist on v3 SSL
# 10 = insist on TLSv1
# 0 or undef = guess (v23)
#
$Net::SSLeay::ssl_version = 0;  # don't change here, use 
                                # Net::SSLeay::version=[2,3,0] in caller

#define to enable the "cat /proc/$$/stat" stuff
$Net::SSLeay::linux_debug = 0;

# Number of seconds to sleep after sending message and before half
# closing connection. Useful with antiquated broken servers.
$Net::SSLeay::slowly = 0;

# RANDOM NUMBER INITIALIZATION
#
# Edit to your taste. Using /dev/random would be more secure, but may
# block if randomness is not available, thus the default is
# /dev/urandom. $how_random determines how many bits of randomness to take
# from the device. You should take enough (read SSLeay/doc/rand), but
# beware that randomness is limited resource so you should not waste
# it either or you may end up with randomness depletion (situation where
# /dev/random would block and /dev/urandom starts to return predictable
# numbers).
#
# N.B. /dev/urandom does not exit on all systems, such as Solaris 2.6. In that
#      case you should get a third party package that emulates /dev/urandom
#      (e.g. via named pipe) or supply a random number file. Some such
#      packages are documented in Caveat section of the POD documentation.

$Net::SSLeay::random_device = '/dev/urandom';
$Net::SSLeay::how_random = 512;

$VERSION = '1.36';
@ISA = qw(Exporter);
@EXPORT_OK = qw(
    AT_MD5_WITH_RSA_ENCRYPTION
    CB_ACCEPT_EXIT
    CB_ACCEPT_LOOP
    CB_CONNECT_EXIT
    CB_CONNECT_LOOP
    CK_DES_192_EDE3_CBC_WITH_MD5
    CK_DES_192_EDE3_CBC_WITH_SHA
    CK_DES_64_CBC_WITH_MD5
    CK_DES_64_CBC_WITH_SHA
    CK_DES_64_CFB64_WITH_MD5_1
    CK_IDEA_128_CBC_WITH_MD5
    CK_NULL
    CK_NULL_WITH_MD5
    CK_RC2_128_CBC_EXPORT40_WITH_MD5
    CK_RC2_128_CBC_WITH_MD5
    CK_RC4_128_EXPORT40_WITH_MD5
    CK_RC4_128_WITH_MD5
    CLIENT_VERSION
    ERROR_NONE
    ERROR_SSL
    ERROR_SYSCALL
    ERROR_WANT_CONNECT
    ERROR_WANT_READ
    ERROR_WANT_WRITE
    ERROR_WANT_X509_LOOKUP
    ERROR_ZERO_RETURN
    CT_X509_CERTIFICATE
    FILETYPE_ASN1
    FILETYPE_PEM
    F_CLIENT_CERTIFICATE
    F_CLIENT_HELLO
    F_CLIENT_MASTER_KEY
    F_D2I_SSL_SESSION
    F_GET_CLIENT_FINISHED
    F_GET_CLIENT_HELLO
    F_GET_CLIENT_MASTER_KEY
    F_GET_SERVER_FINISHED
    F_GET_SERVER_HELLO
    F_GET_SERVER_VERIFY
    F_I2D_SSL_SESSION
    F_READ_N
    F_REQUEST_CERTIFICATE
    F_SERVER_HELLO
    F_SSL_ACCEPT
    F_SSL_CERT_NEW
    F_SSL_CONNECT
    F_SSL_ENC_DES_CBC_INIT
    F_SSL_ENC_DES_CFB_INIT
    F_SSL_ENC_DES_EDE3_CBC_INIT
    F_SSL_ENC_IDEA_CBC_INIT
    F_SSL_ENC_NULL_INIT
    F_SSL_ENC_RC2_CBC_INIT
    F_SSL_ENC_RC4_INIT
    F_SSL_GET_NEW_SESSION
    F_SSL_MAKE_CIPHER_LIST
    F_SSL_NEW
    F_SSL_READ
    F_SSL_RSA_PRIVATE_DECRYPT
    F_SSL_RSA_PUBLIC_ENCRYPT
    F_SSL_SESSION_NEW
    F_SSL_SESSION_PRINT_FP
    F_SSL_SET_CERTIFICATE
    F_SSL_SET_FD
    F_SSL_SET_RFD
    F_SSL_SET_WFD
    F_SSL_STARTUP
    F_SSL_USE_CERTIFICATE
    F_SSL_USE_CERTIFICATE_ASN1
    F_SSL_USE_CERTIFICATE_FILE
    F_SSL_USE_PRIVATEKEY
    F_SSL_USE_PRIVATEKEY_ASN1
    F_SSL_USE_PRIVATEKEY_FILE
    F_SSL_USE_RSAPRIVATEKEY
    F_SSL_USE_RSAPRIVATEKEY_ASN1
    F_SSL_USE_RSAPRIVATEKEY_FILE
    F_WRITE_PENDING
    GEN_OTHERNAME
    GEN_EMAIL
    GEN_DNS
    GEN_X400
    GEN_DIRNAME
    GEN_EDIPARTY
    GEN_URI
    GEN_IPADD
    GEN_RID
    MAX_MASTER_KEY_LENGTH_IN_BITS
    MAX_RECORD_LENGTH_2_BYTE_HEADER
    MAX_RECORD_LENGTH_3_BYTE_HEADER
    MAX_SSL_SESSION_ID_LENGTH_IN_BYTES
    MIN_RSA_MODULUS_LENGTH_IN_BYTES
    MT_CLIENT_CERTIFICATE
    MT_CLIENT_FINISHED
    MT_CLIENT_HELLO
    MT_CLIENT_MASTER_KEY
    MT_ERROR
    MT_REQUEST_CERTIFICATE
    MT_SERVER_FINISHED
    MT_SERVER_HELLO
    MT_SERVER_VERIFY
    NOTHING
    NID_undef
    NID_algorithm
    NID_rsadsi
    NID_pkcs
    NID_md2
    NID_md5
    NID_rc4
    NID_rsaEncryption
    NID_md2WithRSAEncryption
    NID_md5WithRSAEncryption
    NID_pbeWithMD2AndDES_CBC
    NID_pbeWithMD5AndDES_CBC
    NID_X500
    NID_X509
    NID_commonName
    NID_countryName
    NID_localityName
    NID_stateOrProvinceName
    NID_organizationName
    NID_organizationalUnitName
    NID_rsa
    NID_pkcs7
    NID_pkcs7_data
    NID_pkcs7_signed
    NID_pkcs7_enveloped
    NID_pkcs7_signedAndEnveloped
    NID_pkcs7_digest
    NID_pkcs7_encrypted
    NID_pkcs3
    NID_dhKeyAgreement
    NID_des_ecb
    NID_des_cfb64
    NID_des_cbc
    NID_des_ede
    NID_des_ede3
    NID_idea_cbc
    NID_idea_cfb64
    NID_idea_ecb
    NID_rc2_cbc
    NID_rc2_ecb
    NID_rc2_cfb64
    NID_rc2_ofb64
    NID_sha
    NID_shaWithRSAEncryption
    NID_des_ede_cbc
    NID_des_ede3_cbc
    NID_des_ofb64
    NID_idea_ofb64
    NID_pkcs9
    NID_pkcs9_emailAddress
    NID_pkcs9_unstructuredName
    NID_pkcs9_contentType
    NID_pkcs9_messageDigest
    NID_pkcs9_signingTime
    NID_pkcs9_countersignature
    NID_pkcs9_challengePassword
    NID_pkcs9_unstructuredAddress
    NID_pkcs9_extCertAttributes
    NID_netscape
    NID_netscape_cert_extension
    NID_netscape_data_type
    NID_des_ede_cfb64
    NID_des_ede3_cfb64
    NID_des_ede_ofb64
    NID_des_ede3_ofb64
    NID_sha1
    NID_sha1WithRSAEncryption
    NID_dsaWithSHA
    NID_dsa_2
    NID_pbeWithSHA1AndRC2_CBC
    NID_id_pbkdf2
    NID_dsaWithSHA1_2
    NID_netscape_cert_type
    NID_netscape_base_url
    NID_netscape_revocation_url
    NID_netscape_ca_revocation_url
    NID_netscape_renewal_url
    NID_netscape_ca_policy_url
    NID_netscape_ssl_server_name
    NID_netscape_comment
    NID_netscape_cert_sequence
    NID_desx_cbc
    NID_id_ce
    NID_subject_key_identifier
    NID_key_usage
    NID_private_key_usage_period
    NID_subject_alt_name
    NID_issuer_alt_name
    NID_basic_constraints
    NID_crl_number
    NID_certificate_policies
    NID_authority_key_identifier
    NID_bf_cbc
    NID_bf_ecb
    NID_bf_cfb64
    NID_bf_ofb64
    NID_mdc2
    NID_mdc2WithRSA
    NID_rc4_40
    NID_rc2_40_cbc
    NID_givenName
    NID_surname
    NID_initials
    NID_uniqueIdentifier
    NID_crl_distribution_points
    NID_md5WithRSA
    NID_serialNumber
    NID_title
    NID_description
    NID_cast5_cbc
    NID_cast5_ecb
    NID_cast5_cfb64
    NID_cast5_ofb64
    NID_pbeWithMD5AndCast5_CBC
    NID_dsaWithSHA1
    NID_md5_sha1
    NID_sha1WithRSA
    NID_dsa
    NID_ripemd160
    NID_ripemd160WithRSA
    NID_rc5_cbc
    NID_rc5_ecb
    NID_rc5_cfb64
    NID_rc5_ofb64
    NID_rle_compression
    NID_zlib_compression
    NID_ext_key_usage
    NID_id_pkix
    NID_id_kp
    NID_server_auth
    NID_client_auth
    NID_code_sign
    NID_email_protect
    NID_time_stamp
    NID_ms_code_ind
    NID_ms_code_com
    NID_ms_ctl_sign
    NID_ms_sgc
    NID_ms_efs
    NID_ns_sgc
    NID_delta_crl
    NID_crl_reason
    NID_invalidity_date
    NID_sxnet
    NID_pbe_WithSHA1And128BitRC4
    NID_pbe_WithSHA1And40BitRC4
    NID_pbe_WithSHA1And3_Key_TripleDES_CBC
    NID_pbe_WithSHA1And2_Key_TripleDES_CBC
    NID_pbe_WithSHA1And128BitRC2_CBC
    NID_pbe_WithSHA1And40BitRC2_CBC
    NID_keyBag
    NID_pkcs8ShroudedKeyBag
    NID_certBag
    NID_crlBag
    NID_secretBag
    NID_safeContentsBag
    NID_friendlyName
    NID_localKeyID
    NID_x509Certificate
    NID_sdsiCertificate
    NID_x509Crl
    NID_pbes2
    NID_pbmac1
    NID_hmacWithSHA1
    NID_id_qt_cps
    NID_id_qt_unotice
    NID_rc2_64_cbc
    NID_SMIMECapabilities
    NID_pbeWithMD2AndRC2_CBC
    NID_pbeWithMD5AndRC2_CBC
    NID_pbeWithSHA1AndDES_CBC
    NID_ms_ext_req
    NID_ext_req
    NID_name
    NID_dnQualifier
    NID_id_pe
    NID_id_ad
    NID_info_access
    NID_ad_OCSP
    NID_ad_ca_issuers
    NID_OCSP_sign
    OPENSSL_VERSION_NUMBER
    PE_BAD_CERTIFICATE
    PE_NO_CERTIFICATE
    PE_NO_CIPHER
    PE_UNSUPPORTED_CERTIFICATE_TYPE
    READING
    RECEIVED_SHUTDOWN
    RWERR_BAD_MAC_DECODE
    RWERR_BAD_WRITE_RETRY
    RWERR_INTERNAL_ERROR
    R_BAD_AUTHENTICATION_TYPE
    R_BAD_CHECKSUM
    R_BAD_MAC_DECODE
    R_BAD_RESPONSE_ARGUMENT
    R_BAD_SSL_FILETYPE
    R_BAD_SSL_SESSION_ID_LENGTH
    R_BAD_STATE
    R_BAD_WRITE_RETRY
    R_CHALLENGE_IS_DIFFERENT
    R_CIPHER_CODE_TOO_LONG
    R_CIPHER_TABLE_SRC_ERROR
    R_CONECTION_ID_IS_DIFFERENT
    R_INVALID_CHALLENGE_LENGTH
    R_NO_CERTIFICATE_SET
    R_NO_CERTIFICATE_SPECIFIED
    R_NO_CIPHER_LIST
    R_NO_CIPHER_MATCH
    R_NO_CIPHER_WE_TRUST
    R_NO_PRIVATEKEY
    R_NO_PUBLICKEY
    R_NO_READ_METHOD_SET
    R_NO_WRITE_METHOD_SET
    R_NULL_SSL_CTX
    R_PEER_DID_NOT_RETURN_A_CERTIFICATE
    R_PEER_ERROR
    R_PEER_ERROR_CERTIFICATE
    R_PEER_ERROR_NO_CIPHER
    R_PEER_ERROR_UNSUPPORTED_CERTIFICATE_TYPE
    R_PERR_ERROR_NO_CERTIFICATE
    R_PUBLIC_KEY_ENCRYPT_ERROR
    R_PUBLIC_KEY_IS_NOT_RSA
    R_PUBLIC_KEY_NO_RSA
    R_READ_WRONG_PACKET_TYPE
    R_REVERSE_KEY_ARG_LENGTH_IS_WRONG
    R_REVERSE_MASTER_KEY_LENGTH_IS_WRONG
    R_REVERSE_SSL_SESSION_ID_LENGTH_IS_WRONG
    R_SHORT_READ
    R_SSL_SESSION_ID_IS_DIFFERENT
    R_UNABLE_TO_EXTRACT_PUBLIC_KEY
    R_UNDEFINED_INIT_STATE
    R_UNKNOWN_REMOTE_ERROR_TYPE
    R_UNKNOWN_STATE
    R_UNSUPORTED_CIPHER
    R_WRONG_PUBLIC_KEY_TYPE
    R_X509_LIB
    RSA_3
    RSA_F4
    SENT_SHUTDOWN
    SERVER_VERSION
    SESSION
    SESSION_ASN1_VERSION
    ST_ACCEPT
    ST_BEFORE
    ST_CLIENT_START_ENCRYPTION
    ST_CONNECT
    ST_GET_CLIENT_FINISHED_A
    ST_GET_CLIENT_FINISHED_B
    ST_GET_CLIENT_HELLO_A
    ST_GET_CLIENT_HELLO_B
    ST_GET_CLIENT_MASTER_KEY_A
    ST_GET_CLIENT_MASTER_KEY_B
    ST_GET_SERVER_FINISHED_A
    ST_GET_SERVER_FINISHED_B
    ST_GET_SERVER_HELLO_A
    ST_GET_SERVER_HELLO_B
    ST_GET_SERVER_VERIFY_A
    ST_GET_SERVER_VERIFY_B
    ST_INIT
    ST_OK
    ST_READ_BODY
    ST_READ_HEADER
    ST_SEND_CLIENT_CERTIFICATE_A
    ST_SEND_CLIENT_CERTIFICATE_B
    ST_SEND_CLIENT_CERTIFICATE_C
    ST_SEND_CLIENT_CERTIFICATE_D
    ST_SEND_CLIENT_FINISHED_A
    ST_SEND_CLIENT_FINISHED_B
    ST_SEND_CLIENT_HELLO_A
    ST_SEND_CLIENT_HELLO_B
    ST_SEND_CLIENT_MASTER_KEY_A
    ST_SEND_CLIENT_MASTER_KEY_B
    ST_SEND_REQUEST_CERTIFICATE_A
    ST_SEND_REQUEST_CERTIFICATE_B
    ST_SEND_REQUEST_CERTIFICATE_C
    ST_SEND_REQUEST_CERTIFICATE_D
    ST_SEND_SERVER_FINISHED_A
    ST_SEND_SERVER_FINISHED_B
    ST_SEND_SERVER_HELLO_A
    ST_SEND_SERVER_HELLO_B
    ST_SEND_SERVER_VERIFY_A
    ST_SEND_SERVER_VERIFY_B
    ST_SERVER_START_ENCRYPTION
    ST_X509_GET_CLIENT_CERTIFICATE
    ST_X509_GET_SERVER_CERTIFICATE
    TXT_DES_192_EDE3_CBC_WITH_MD5
    TXT_DES_192_EDE3_CBC_WITH_SHA
    TXT_DES_64_CBC_WITH_MD5
    TXT_DES_64_CBC_WITH_SHA
    TXT_DES_64_CFB64_WITH_MD5_1
    TXT_IDEA_128_CBC_WITH_MD5
    TXT_NULL
    TXT_NULL_WITH_MD5
    TXT_RC2_128_CBC_EXPORT40_WITH_MD5
    TXT_RC2_128_CBC_WITH_MD5
    TXT_RC4_128_EXPORT40_WITH_MD5
    TXT_RC4_128_WITH_MD5
    VERIFY_CLIENT_ONCE
    VERIFY_FAIL_IF_NO_PEER_CERT
    VERIFY_NONE
    VERIFY_PEER
    WRITING
    X509_LOOKUP
    X509_V_FLAG_CB_ISSUER_CHECK
    X509_V_FLAG_USE_CHECK_TIME
    X509_V_FLAG_CRL_CHECK
    X509_V_FLAG_CRL_CHECK_ALL
    X509_V_FLAG_IGNORE_CRITICAL
    CTX_new
    CTX_v2_new
    CTX_v3_new
    CTX_v23_new
    CTX_free
    new
    free
    accept
    clear
    connect
    set_fd
    set_rfd
    set_wfd
    get_fd
    read
    write
    peek
    use_RSAPrivateKey
    use_RSAPrivateKey_ASN1
    use_RSAPrivateKey_file
    CTX_use_RSAPrivateKey_file
    use_PrivateKey
    use_PrivateKey_ASN1
    use_PrivateKey_file
    use_certificate
    use_certificate_ASN1
    use_certificate_file
    CTX_use_certificate_file
    load_error_strings
    ERR_load_SSL_strings
    ERR_load_RAND_strings
    state_string
    rstate_string
    state_string_long
    rstate_string_long
    get_time
    set_time
    get_timeout
    set_timeout
    copy_session_id
    set_read_ahead
    get_read_ahead
    pending
    get_cipher_list
    set_cipher_list
    get_cipher
    get_shared_ciphers
    get_peer_certificate
    set_verify
    flush_sessions
    set_bio
    get_rbio
    get_wbio
    SESSION_new
    SESSION_print
    SESSION_free
    i2d_SSL_SESSION
    set_session
    add_session
    remove_session
    d2i_SSL_SESSION
    BIO_f_ssl
    BIO_new
    BIO_new_file
    BIO_s_mem
    BIO_free
    BIO_read
    BIO_write
    BIO_eof
    BIO_pending
    BIO_wpending
    ERR_get_error
    ERR_error_string
    err
    clear_error
    X509_get_issuer_name
    X509_get_subject_name
    X509_NAME_oneline
    X509_NAME_get_text_by_NID
    CTX_get_cert_store
    X509_STORE_add_cert
    X509_STORE_add_crl
    X509_STORE_CTX_set_flags
    X509_load_cert_file
    X509_load_crl_file
    X509_load_cert_crl_file
    PEM_read_bio_X509_CRL
    die_if_ssl_error
    die_now
    print_errs
    set_cert_and_key
    set_server_cert_and_key
    make_form
    make_headers
    do_https
    get_https
    post_https
    get_https4
    post_https4
    sslcat
    ssl_read_CRLF
    ssl_read_all
    ssl_read_until
    ssl_write_CRLF
    ssl_write_all
    get_http
    post_http
    get_httpx
    post_httpx
    get_https3
    post_https3
    get_http4
    post_http4
    get_httpx4
    post_httpx4
    tcpcat
    tcpxcat
    tcp_read_CRLF
    tcp_read_all
    tcp_read_until
    tcp_write_CRLF
    tcp_write_all
    dump_peer_certificate
    RSA_generate_key
    RSA_free
    X509_free
    SESSION_get_master_key
    get_client_random
    get_server_random
);

sub AUTOLOAD {
    # This AUTOLOAD is used to 'autoload' constants from the constant()
    # XS function.  If a constant is not found then control is passed
    # to the AUTOLOAD in AutoLoader.

    my $constname;
    ($constname = $AUTOLOAD) =~ s/.*:://;
    my $val = constant($constname);
    if ($! != 0) {
	if ($! =~ /((Invalid)|(not valid))/i || $!{EINVAL}) {
	    $AutoLoader::AUTOLOAD = $AUTOLOAD;
	    goto &AutoLoader::AUTOLOAD;
	}
	else {
	  croak "Your vendor has not defined SSLeay macro $constname";
	}
    }
    eval "sub $AUTOLOAD { $val }";
    goto &$AUTOLOAD;
}

eval {
	require XSLoader;
	XSLoader::load('Net::SSLeay', $VERSION);
	1;
} or do {
	require DynaLoader;
	push @ISA, 'DynaLoader';
	bootstrap Net::SSLeay $VERSION;
};

# Preloaded methods go here.

$CRLF = "\x0d\x0a";  # because \r\n is not fully portable

### Print SSLeay error stack

sub print_errs {
    my ($msg) = @_;
    my ($count, $err, $errs, $e) = (0,0,'');
    while ($err = ERR_get_error()) {
        $count ++;
	$e = "$msg $$: $count - " . ERR_error_string($err) . "\n";
	$errs .= $e;
	warn $e if $Net::SSLeay::trace;
    }
    return $errs;
}

# Death is conditional to SSLeay errors existing, i.e. this function checks
# for errors and only dies in affirmative.
# usage: Net::SSLeay::write($ssl, "foo") or die_if_ssl_error("SSL write ($!)");

sub die_if_ssl_error {
    my ($msg) = @_;    
    die "$$: $msg\n" if print_errs($msg);
}

# Unconditional death. Used to print SSLeay errors before dying.
# usage: Net::SSLeay::connect($ssl) or die_now("Failed SSL connect ($!)");

sub die_now {
    my ($msg) = @_;    
    print_errs($msg);
    die "$$: $msg\n";
}

# Perl 5.6.* unicode support causes that length() no longer reliably
# reflects the byte length of a string. This eval is to fix that.
# Thanks to Sean Burke for the snippet.

BEGIN{ 
eval 'use bytes; sub blength ($) { length $_[0] }'; 
$@ and eval '    sub blength ($) { length $_[0] }' ; 
}

# Autoload methods go after =cut, and are processed by the autosplit program.

1;
__END__
# Documentation. Use `perl-root/pod/pod2html SSLeay.pm` to output html

=head1 NAME

Net::SSLeay - Perl extension for using OpenSSL

=head1 SYNOPSIS

  use Net::SSLeay qw(get_https post_https sslcat make_headers make_form);

  ($page) = get_https('www.bacus.pt', 443, '/');                 # 1

  ($page, $response, %reply_headers)
	 = get_https('www.bacus.pt', 443, '/',                   # 2
	 	make_headers(User-Agent => 'Cryptozilla/5.0b1',
			     Referer    => 'https://www.bacus.pt'
		));

  ($page, $result, %headers) =                                   # 2b
         = get_https('www.bacus.pt', 443, '/protected.html',
	      make_headers(Authorization =>
			   'Basic ' . MIME::Base64::encode("$user:$pass",''))
	      );

  ($page, $response, %reply_headers)
	 = post_https('www.bacus.pt', 443, '/foo.cgi', '',       # 3
		make_form(OK   => '1',
			  name => 'Sampo'
		));

  $reply = sslcat($host, $port, $request);                       # 4

  ($reply, $err, $server_cert) = sslcat($host, $port, $request); # 5

  $Net::SSLeay::trace = 2;  # 0=no debugging, 1=ciphers, 2=trace, 3=dump data

=head1 DESCRIPTION

There is a related module called C<Net::SSLeay::Handle> included in this
distribution that you might want to use instead. It has its own pod
documentation.

This module offers some high level convenience functions for accessing
web pages on SSL servers (for symmetry, the same API is offered for
accessing http servers, too), an C<sslcat()> function for writing your own
clients, and finally access to the SSL api of the SSLeay/OpenSSL package
so you can write servers or clients for more complicated applications.

For high level functions it is most convenient to import them into your
main namespace as indicated in the synopsis.

Case 1 demonstrates the typical invocation of get_https() to fetch an HTML
page from secure server. The first argument provides the hostname or IP
in dotted decimal notation of the remote server to contact. The second
argument is the TCP port at the remote end (your own port is picked
arbitrarily from high numbered ports as usual for TCP). The third
argument is the URL of the page without the host name part. If in
doubt consult the HTTP specifications at L<http://www.w3c.org>.

Case 2 demonstrates full fledged use of C<get_https()>. As can be seen,
C<get_https()> parses the response and response headers and returns them as
a list, which can be captured in a hash for later reference. Also a
fourth argument to C<get_https()> is used to insert some additional headers
in the request. C<make_headers()> is a function that will convert a list or
hash to such headers. By default C<get_https()> supplies C<Host> (to make
virtual hosting easy) and C<Accept> (reportedly needed by IIS) headers.

Case 2b demonstrates how to get a password protected page. Refer to
the HTTP protocol specifications for further details (e.g. RFC-2617).

Case 3 invokes C<post_https()> to submit a HTML/CGI form to a secure
server. The first four arguments are equal to C<get_https()> (note that 
the empty string (C<''>) is passed as header argument).
The fifth argument is the
contents of the form formatted according to CGI specification. In this
case the helper function C<make_https()> is used to do the formatting,
but you could pass any string. C<post_https()> automatically adds
C<Content-Type> and C<Content-Length> headers to the request.

Case 4 shows the fundamental C<sslcat()> function (inspired in spirit by
the C<netcat> utility :-). It's your swiss army knife that allows you to
easily contact servers, send some data, and then get the response. You
are responsible for formatting the data and parsing the response -
C<sslcat()> is just a transport.

Case 5 is a full invocation of C<sslcat()> which allows the return of errors
as well as the server (peer) certificate.

The C<$trace> global variable can be used to control the verbosity of the 
high level functions. Level 0 guarantees silence, level 1 (the default)
only emits error messages.

=head2 Alternate versions of the API

The above mentioned functions actually return the response headers as
a list, which only gets converted to hash upon assignment (this
assignment looses information if the same header occurs twice, as may
be the case with cookies). There are also other variants of the
functions that return unprocessed headers and that return a reference
to a hash.

  ($page, $response, @headers) = get_https('www.bacus.pt', 443, '/');
  for ($i = 0; $i < $#headers; $i+=2) {
      print "$headers[$i] = " . $headers[$i+1] . "\n";
  }

  ($page, $response, $headers, $server_cert)
    = get_https3('www.bacus.pt', 443, '/');
  print "$headers\n";

  ($page, $response, %headers_ref, $server_cert)
    = get_https4('www.bacus.pt', 443, '/');
  for $k (sort keys %{headers_ref}) {
      for $v (@{$headers_ref{$k}}) {
	  print "$k = $v\n";
      }
  }

All of the above code fragments accomplish the same thing: display all
values of all headers. The API functions ending in "3" return the
headers simply as a scalar string and it is up to the application to
split them up. The functions ending in "4" return a reference to
a hash of arrays (see L<perlref> and L<perllol> if you are
not familiar with complex perl data structures). To access a single value
of such a header hash you would do something like

  print $headers_ref{COOKIE}[0];

Variants 3 and 4 also allow you to discover the server certificate
in case you would like to store or display it, e.g.

  ($p, $resp, $hdrs, $server_cert) = get_https3('www.bacus.pt', 443, '/');
  if (!defined($server_cert) || ($server_cert == 0)) {
      warn "Subject Name: undefined, Issuer  Name: undefined";
  } else {
      warn 'Subject Name: '
	  . Net::SSLeay::X509_NAME_oneline(
		 Net::SSLeay::X509_get_subject_name($server_cert))
	      . 'Issuer  Name: '
		  . Net::SSLeay::X509_NAME_oneline(
                         Net::SSLeay::X509_get_issuer_name($server_cert));
  }

Beware that this method only allows after the fact verification of
the certificate: by the time C<get_https3()> has returned the https
request has already been sent to the server, whether you decide to
trust it or not. To do the verification correctly you must either
employ the OpenSSL certificate verification framework or use
the lower level API to first connect and verify the certificate
and only then send the http data. See the implementation of C<ds_https3()>
for guidance on how to do this.

=head2 Using client certificates

Secure web communications are encrypted using symmetric crypto keys
exchanged using encryption based on the certificate of the
server. Therefore in all SSL connections the server must have a
certificate. This serves both to authenticate the server to the
clients and to perform the key exchange.

Sometimes it is necessary to authenticate the client as well. Two
options are available: HTTP basic authentication and a client side
certificate. The basic authentication over HTTPS is actually quite
safe because HTTPS guarantees that the password will not travel in
the clear. Never-the-less, problems like easily guessable passwords
remain. The client certificate method involves authentication of the
client at the SSL level using a certificate. For this to work, both the
client and the server have certificates (which typically are
different) and private keys.

The API functions outlined above accept additional arguments that
allow one to supply the client side certificate and key files. The
format of these files is the same as used for server certificates and
the caveat about encrypting private keys applies.

  ($page, $result, %headers) =                                   # 2c
         = get_https('www.bacus.pt', 443, '/protected.html',
	      make_headers(Authorization =>
			   'Basic ' . MIME::Base64::encode("$user:$pass",'')),
	      '', $mime_type6, $path_to_crt7, $path_to_key8);

  ($page, $response, %reply_headers)
	 = post_https('www.bacus.pt', 443, '/foo.cgi',           # 3b
	      make_headers('Authorization' =>
			   'Basic ' . MIME::Base64::encode("$user:$pass",'')),
	      make_form(OK   => '1', name => 'Sampo'),
	      $mime_type6, $path_to_crt7, $path_to_key8);

Case 2c demonstrates getting a password protected page that also requires
a client certificate, i.e. it is possible to use both authentication
methods simultaneously.

Case 3b is a full blown POST to a secure server that requires both password
authentication and a client certificate, just like in case 2c.

Note: The client will not send a certificate unless the server requests one.
This is typically achieved by setting the verify mode to C<VERIFY_PEER> on the
server:

  Net::SSLeay::set_verify(ssl, Net::SSLeay::VERIFY_PEER, 0);

See C<perldoc ~openssl/doc/ssl/SSL_CTX_set_verify.pod> for a full description.

=head2 Working through a web proxy

C<Net::SSLeay> can use a web proxy to make its connections. You need to
first set the proxy host and port using C<set_proxy()> and then just
use the normal API functions, e.g:

  Net::SSLeay::set_proxy('gateway.myorg.com', 8080);
  ($page) = get_https('www.bacus.pt', 443, '/');

If your proxy requires authentication, you can supply a username and
password as well

  Net::SSLeay::set_proxy('gateway.myorg.com', 8080, 'joe', 'salainen');
  ($page, $result, %headers) =
         = get_https('www.bacus.pt', 443, '/protected.html',
	      make_headers(Authorization =>
			   'Basic ' . MIME::Base64::encode("susie:pass",''))
	      );

This example demonstrates the case where we authenticate to the proxy as
C<"joe"> and to the final web server as C<"susie">. Proxy authentication
requires the C<MIME::Base64> module to work.

=head2 Certificate verification and Certificate Revoocation Lists (CRLs)

OpenSSL supports the ability to verify peer certificates. It can also
optionally check the peer certificate against a Certificate Revocation
List (CRL) from the certificates issuer. A CRL is a file, created by
the certificate issuer that lists all the certificates that it
previously signed, but which it now revokes. CRLs are in PEM format.

You can enable C<Net::SSLeay CRL> checking like this:

	    &Net::SSLeay::X509_STORE_CTX_set_flags
		(&Net::SSLeay::CTX_get_cert_store($ssl), 
		 &Net::SSLeay::X509_V_FLAG_CRL_CHECK); 

After setting this flag, if OpenSSL checks a peer's certificate, then
it will attempt to find a CRL for the issuer. It does this by looking
for a specially named file in the search directory specified by
CTX_load_verify_locations.  CRL files are named with the hash of the
issuer's subject name, followed by C<.r0>, C<.r1> etc.  For example
C<ab1331b2.r0>, C<ab1331b2.r1>. It will read all the .r files for the
issuer, and then check for a revocation of the peer cerificate in all
of them.  (You can also force it to look in a specific named CRL
file., see below).  You can find out the hash of the issuer subject
name in a CRL with

	openssl crl -in crl.pem -hash -noout

If the peer certificate does not pass the revocation list, or if no
CRL is found, then the handshaking fails with an error.

You can also force OpenSSL to look for CRLs in one or more arbitrarily
named files.

    my $bio = Net::SSLeay::BIO_new_file($crlfilename, 'r');
    my $crl = Net::SSLeay::PEM_read_bio_X509_CRL($bio);
    if ($crl) {
        Net::SSLeay::X509_STORE_add_crl(Net::SSLeay::CTX_get_cert_store($ssl, $crl);
    } else {
        error reading CRL....
    }


=head2 Convenience routines

To be used with Low level API

    Net::SSLeay::randomize($rn_seed_file,$additional_seed);
    Net::SSLeay::set_cert_and_key($ctx, $cert_path, $key_path);
    $cert = Net::SSLeay::dump_peer_certificate($ssl);
    Net::SSLeay::ssl_write_all($ssl, $message) or die "ssl write failure";
    $got = Net::SSLeay::ssl_read_all($ssl) or die "ssl read failure";

    $got = Net::SSLeay::ssl_read_CRLF($ssl [, $max_length]);
    $got = Net::SSLeay::ssl_read_until($ssl [, $delimit [, $max_length]]);
    Net::SSLeay::ssl_write_CRLF($ssl, $message);

C<randomize()> seeds the openssl PRNG with C</dev/urandom> (see the top of C<SSLeay.pm>
for how to change or configure this) and optionally with user provided
data. It is very important to properly seed your random numbers, so
do not forget to call this. The high level API functions automatically
call C<randomize()> so it is not needed with them. See also caveats.

C<set_cert_and_key()> takes two file names as arguments and sets
the certificate and private key to those. This can be used to
set either cerver certificates or client certificates.

C<dump_peer_certificate()> allows you to get a plaintext description of the
certificate the peer (usually the server) presented to us.

C<ssl_read_all()> and C<ssl_write_all()> provide true blocking semantics for
these operations (see limitation, below, for explanation). These are
much preferred to the low level API equivalents (which implement BSD
blocking semantics). The message argument to C<ssl_write_all()> can be
a reference. This is helpful to avoid unnecessary copying when writing
something big, e.g:

    $data = 'A' x 1000000000;
    Net::SSLeay::ssl_write_all($ssl, \$data) or die "ssl write failed";

C<ssl_read_CRLF()> uses C<ssl_read_all()> to read in a line terminated with a
carriage return followed by a linefeed (CRLF).  The CRLF is included in
the returned scalar.

C<ssl_read_until()> uses C<ssl_read_all()> to read from the SSL input
stream until it encounters a programmer specified delimiter.
If the delimiter is undefined, C<$/> is used.  If C<$/> is undefined,
C<\n> is used.  One can optionally set a maximum length of bytes to read
from the SSL input stream.

C<ssl_write_CRLF()> writes C<$message> and appends CRLF to the SSL output stream.

=head2 Low level API

In addition to the high level functions outlined above, this module
contains straight-forward access to SSL part of OpenSSL C api. Only the SSL
subpart of OpenSSL is implemented (if anyone wants to implement other
parts, feel free to submit patches).

See the C<ssl.h> header from OpenSSL C distribution for a list of low level
SSLeay functions to call (check SSLeay.xs to see if some function has been
implemented). The module strips the initial C<"SSL_"> off of the SSLeay names. Generally you should use C<Net::SSLeay::> in its
place. For example:

In C:

	#include <ssl.h>

	err = SSL_set_verify (ssl, SSL_VERIFY_CLIENT_ONCE,
				   &your_call_back_here);

In Perl:

	use Net::SSLeay;

	$err = Net::SSLeay::set_verify ($ssl,
					Net::SSLeay::VERIFY_CLIENT_ONCE,
					\&your_call_back_here);

If the function does not start with C<SSL_> you should use the full
function name, e.g.:

	$err = Net::SSLeay::ERR_get_error;

The following new functions behave in perlish way:

	$got = Net::SSLeay::read($ssl);
                                    # Performs SSL_read, but returns $got
                                    # resized according to data received.
                                    # Returns undef on failure.

	Net::SSLeay::write($ssl, $foo) || die;
                                    # Performs SSL_write, but automatically
                                    # figures out the size of $foo

In order to use the low level API you should start your programs with
the following incantation:

	use Net::SSLeay qw(die_now die_if_ssl_error);
	Net::SSLeay::load_error_strings();
	Net::SSLeay::SSLeay_add_ssl_algorithms();    # Important!
        Net::SSLeay::ENGINE_load_builtin_engines();  # If you want built-in engines
        Net::SSLeay::ENGINE_register_all_complete(); # If you want built-in engines
        Net::SSLeay::randomize();

C<die_now()> and C<die_if_ssl_error()> are used to conveniently print the SSLeay error stack when something goes wrong, thusly:

	Net::SSLeay::connect($ssl) or die_now("Failed SSL connect ($!)");
	Net::SSLeay::write($ssl, "foo") or die_if_ssl_error("SSL write ($!)");

You can also use C<Net::SSLeay::print_errs()> to dump the error stack without
exiting the program. As can be seen, your code becomes much more readable
if you import the error reporting functions into your main name space.

I can not emphasize the need to check for error enough. Use these
functions even in the most simple programs, they will reduce debugging
time greatly. Do not ask questions on the mailing list without having
first sprinkled these in your code.

=head2 Sockets

Perl uses file handles for all I/O. While SSLeay has a quite flexible BIO
mechanism and perl has an evolved PerlIO mechanism, this module still
sticks to using file descriptors. Thus to attach SSLeay to a socket you
should use C<fileno()> to extract the underlying file descriptor:

    Net::SSLeay::set_fd($ssl, fileno(S));   # Must use fileno

You should also set C<$|> to 1 to eliminate STDIO buffering so you do not
get confused if you use perl I/O functions to manipulate your socket
handle.

If you need to C<select(2)> on the socket, go right ahead, but be warned
that OpenSSL does some internal buffering so SSL_read does not always
return data even if the socket selected for reading (just keep on
selecting and trying to read). C<Net::SSLeay> is no different from the
C language OpenSSL in this respect.

=head2 Callbacks

You can establish a per-context verify callback function something like this:

	sub verify {
	    my ($ok, $x509_store_ctx) = @_;
	    print "Verifying certificate...\n";
		...
	    return $ok;
	}

It is used like this:

	Net::SSLeay::set_verify ($ssl, Net::SSLeay::VERIFY_PEER, \&verify);

Per-context callbacks for decrypting private keys are implemented.

        Net::SSLeay::CTX_set_default_passwd_cb($ctx, sub { "top-secret" });
        Net::SSLeay::CTX_use_PrivateKey_file($ctx, "key.pem",
					     Net::SSLeay::FILETYPE_PEM)
            or die "Error reading private key";
        Net::SSLeay::CTX_set_default_passwd_cb($ctx, undef);

If Hello Extensions are supported by your OpenSSL, 
a session secret callback can be set up to be called when a session secret is set
by openssl.

Establish it like this:
    Net::SSLeay::set_session_secret_cb($ssl, \&session_secret_cb, $somedata);

It will be called like this:

    sub session_secret_cb
    {
        my ($secret, \@cipherlist, \$preferredcipher, $somedata) = @_;
    }


No other callbacks are implemented. You do not need to use any
callback for simple (i.e. normal) cases where the SSLeay built-in
verify mechanism satisfies your needs.

It is required to reset these callbacks to undef immediately after use to prevent 
memory leaks, thread safety problems and crashes on exit that 
can occur if different threads set different callbacks. 

If you want to use callback stuff, see examples/callback.pl! Its the
only one I am able to make work reliably.

=head2 X509 and RAND stuff

This module largely lacks interface to the X509 and RAND routines, but
as I was lazy and needed them, the following kludges are implemented:

    $x509_name = Net::SSLeay::X509_get_subject_name($x509_cert);
    $x509_name = Net::SSLeay::X509_get_issuer_name($x509_cert);
    print Net::SSLeay::X509_NAME_oneline($x509_name);
    $text = Net::SSLeay::X509_NAME_get_text_by_NID($name, $nid);

    ($type1, $subject1, $type2, $subject2, ...) =
       Net::SSLeay::X509_get_subjectAltNames($x509_cert)

    subjectAltName types as per x509v3.h GEN_*, for example
    GEN_DNS or GEN_IPADD which can be imported.

    Net::SSLeay::RAND_seed($buf);   # Perlishly figures out buf size
    Net::SSLeay::RAND_bytes($buf, $num);
    Net::SSLeay::RAND_pseudo_bytes($buf, $num);
    Net::SSLeay::RAND_add($buf, $num, $entropy);
    Net::SSLeay::RAND_poll();
    Net::SSLeay::RAND_status();
    Net::SSLeay::RAND_cleanup();
    Net::SSLeay::RAND_file_name($num);
    Net::SSLeay::RAND_load_file($file_name, $how_many_bytes);
    Net::SSLeay::RAND_write_file($file_name);
    Net::SSLeay::RAND_egd($path);
    Net::SSLeay::RAND_egd_bytes($path, $bytes);

Actually you should consider using the following helper functions:

    print Net::SSLeay::dump_peer_certificate($ssl);
    Net::SSLeay::randomize();

=head2 RSA interface

Some RSA functions are available:

    $rsakey = Net::SSLeay::RSA_generate_key();
    Net::SSLeay::CTX_set_tmp_rsa($ctx, $rsakey);
    Net::SSLeay::RSA_free($rsakey);

=head2 Digests

Some Digest functions are available if supported by the underlying
library.  These may include MD2, MD4, MD5, and RIPEMD160:

    $hash = Net::SSLeay::MD5($foo);
    print unpack('H*', $hash);

=head2 BIO interface

Some BIO functions are available:

    Net::SSLeay::BIO_s_mem();
    $bio = Net::SSLeay::BIO_new(BIO_s_mem())
    $bio = Net::SSLeay::BIO_new_file($filename, $mode);
    Net::SSLeay::BIO_free($bio)
    $count = Net::SSLeay::BIO_write($data);
    $data = Net::SSLeay::BIO_read($bio);
    $data = Net::SSLeay::BIO_read($bio, $maxbytes);
    $is_eof = Net::SSLeay::BIO_eof($bio);
    $count = Net::SSLeay::BIO_pending($bio);
    $count = Net::SSLeay::BIO_wpending ($bio);

=head2 Low level API

Some very low level API functions are available:

    $client_random = Net::SSLeay::get_client_random($ssl);
    $server_random = Net::SSLeay::get_server_random($ssl);
    $session = Net::SSLeay::get_session($ssl);
    $master_key = Net::SSLeay::SESSION_get_master_key($session);
    Net::SSLeay::SESSION_set_master_key($session, $master_secret);
    $keyblocksize = Net::SSLeay::get_keyblock_size($session);

=head2 HTTP (without S) API

Over the years it has become clear that it would be convenient to use
the light-weight flavour API of C<Net::SSLeay> for normal HTTP as well (see
C<LWP> for the heavy-weight object-oriented approach). In fact it would be
nice to be able to flip https on and off on the fly. Thus regular HTTP
support was evolved.

  use Net::SSLeay qw(get_http post_http tcpcat
                      get_httpx post_httpx tcpxcat
                      make_headers make_form);

  ($page, $result, %headers) =
         = get_http('www.bacus.pt', 443, '/protected.html',
	      make_headers(Authorization =>
			   'Basic ' . MIME::Base64::encode("$user:$pass",''))
	      );

  ($page, $response, %reply_headers)
	 = post_http('www.bacus.pt', 443, '/foo.cgi', '',
		make_form(OK   => '1',
			  name => 'Sampo'
		));

  ($reply, $err) = tcpcat($host, $port, $request);

  ($page, $result, %headers) =
         = get_httpx($usessl, 'www.bacus.pt', 443, '/protected.html',
	      make_headers(Authorization =>
			   'Basic ' . MIME::Base64::encode("$user:$pass",''))
	      );

  ($page, $response, %reply_headers)
	 = post_httpx($usessl, 'www.bacus.pt', 443, '/foo.cgi', '',
		make_form(OK   => '1',  name => 'Sampo'	));

  ($reply, $err, $server_cert) = tcpxcat($usessl, $host, $port, $request);

As can be seen, the C<"x"> family of APIs takes as the first argument a flag
which indicates whether SSL is used or not.

=head1 EXAMPLES

One very good example to look at is the implementation of C<sslcat()> in the
C<SSLeay.pm> file.

The following is a simple SSLeay client (with too little error checking :-(

    #!/usr/local/bin/perl
    use Socket;
    use Net::SSLeay qw(die_now die_if_ssl_error) ;
    Net::SSLeay::load_error_strings();
    Net::SSLeay::SSLeay_add_ssl_algorithms();
    Net::SSLeay::randomize();

    ($dest_serv, $port, $msg) = @ARGV;      # Read command line
    $port = getservbyname ($port, 'tcp') unless $port =~ /^\d+$/;
    $dest_ip = gethostbyname ($dest_serv);
    $dest_serv_params  = sockaddr_in($port, $dest_ip);

    socket  (S, &AF_INET, &SOCK_STREAM, 0)  or die "socket: $!";
    connect (S, $dest_serv_params)          or die "connect: $!";
    select  (S); $| = 1; select (STDOUT);   # Eliminate STDIO buffering

    # The network connection is now open, lets fire up SSL    

    $ctx = Net::SSLeay::CTX_new() or die_now("Failed to create SSL_CTX $!");
    Net::SSLeay::CTX_set_options($ctx, &Net::SSLeay::OP_ALL)
         and die_if_ssl_error("ssl ctx set options");
    $ssl = Net::SSLeay::new($ctx) or die_now("Failed to create SSL $!");
    Net::SSLeay::set_fd($ssl, fileno(S));   # Must use fileno
    $res = Net::SSLeay::connect($ssl) and die_if_ssl_error("ssl connect");
    print "Cipher `" . Net::SSLeay::get_cipher($ssl) . "'\n";

    # Exchange data

    $res = Net::SSLeay::write($ssl, $msg);  # Perl knows how long $msg is
    die_if_ssl_error("ssl write");
    CORE::shutdown S, 1;  # Half close --> No more output, sends EOF to server
    $got = Net::SSLeay::read($ssl);         # Perl returns undef on failure
    die_if_ssl_error("ssl read");
    print $got;

    Net::SSLeay::free ($ssl);               # Tear down connection
    Net::SSLeay::CTX_free ($ctx);
    close S;

The following is a simple SSLeay echo server (non forking):

    #!/usr/local/bin/perl -w
    use Socket;
    use Net::SSLeay qw(die_now die_if_ssl_error);
    Net::SSLeay::load_error_strings();
    Net::SSLeay::SSLeay_add_ssl_algorithms();
    Net::SSLeay::randomize();

    $our_ip = "\0\0\0\0"; # Bind to all interfaces
    $port = 1235;							 
    $sockaddr_template = 'S n a4 x8';
    $our_serv_params = pack ($sockaddr_template, &AF_INET, $port, $our_ip);

    socket (S, &AF_INET, &SOCK_STREAM, 0)  or die "socket: $!";
    bind (S, $our_serv_params)             or die "bind:   $!";
    listen (S, 5)                          or die "listen: $!";
    $ctx = Net::SSLeay::CTX_new ()         or die_now("CTX_new ($ctx): $!");
    Net::SSLeay::CTX_set_options($ctx, &Net::SSLeay::OP_ALL)
         and die_if_ssl_error("ssl ctx set options");

    # Following will ask password unless private key is not encrypted
    Net::SSLeay::CTX_use_RSAPrivateKey_file ($ctx, 'plain-rsa.pem',
                                             &Net::SSLeay::FILETYPE_PEM);
    die_if_ssl_error("private key");
    Net::SSLeay::CTX_use_certificate_file ($ctx, 'plain-cert.pem',
 				           &Net::SSLeay::FILETYPE_PEM);
    die_if_ssl_error("certificate");

    while (1) {    
        print "Accepting connections...\n";
        ($addr = accept (NS, S))           or die "accept: $!";
        select (NS); $| = 1; select (STDOUT);  # Piping hot!

        ($af,$client_port,$client_ip) = unpack($sockaddr_template,$addr);
        @inetaddr = unpack('C4',$client_ip);
        print "$af connection from " .
        join ('.', @inetaddr) . ":$client_port\n";

        # We now have a network connection, lets fire up SSLeay...

        $ssl = Net::SSLeay::new($ctx)      or die_now("SSL_new ($ssl): $!");
        Net::SSLeay::set_fd($ssl, fileno(NS));

        $err = Net::SSLeay::accept($ssl) and die_if_ssl_error('ssl accept');
        print "Cipher `" . Net::SSLeay::get_cipher($ssl) . "'\n";

        # Connected. Exchange some data.

        $got = Net::SSLeay::read($ssl);     # Returns undef on fail
        die_if_ssl_error("ssl read");
        print "Got `$got' (" . length ($got) . " chars)\n";

        Net::SSLeay::write ($ssl, uc ($got)) or die "write: $!";
        die_if_ssl_error("ssl write");

        Net::SSLeay::free ($ssl);           # Tear down connection
        close NS;
    }

Yet another echo server. This one runs from C</etc/inetd.conf> so it avoids
all the socket code overhead. Only caveat is opening an rsa key file -
it had better be without any encryption or else it will not know where
to ask for the password. Note how C<STDIN> and C<STDOUT> are wired to SSL.

    #!/usr/local/bin/perl
    # /etc/inetd.conf
    #    ssltst stream tcp nowait root /path/to/server.pl server.pl
    # /etc/services
    #    ssltst		1234/tcp

    use Net::SSLeay qw(die_now die_if_ssl_error);
    Net::SSLeay::load_error_strings();
    Net::SSLeay::SSLeay_add_ssl_algorithms();
    Net::SSLeay::randomize();

    chdir '/key/dir' or die "chdir: $!";
    $| = 1;  # Piping hot!
    open LOG, ">>/dev/console" or die "Can't open log file $!";
    select LOG; print "server.pl started\n";

    $ctx = Net::SSLeay::CTX_new()     or die_now "CTX_new ($ctx) ($!)";
    $ssl = Net::SSLeay::new($ctx)     or die_now "new ($ssl) ($!)";
    Net::SSLeay::set_options($ssl, &Net::SSLeay::OP_ALL)
         and die_if_ssl_error("ssl set options");

    # We get already open network connection from inetd, now we just
    # need to attach SSLeay to STDIN and STDOUT
    Net::SSLeay::set_rfd($ssl, fileno(STDIN));
    Net::SSLeay::set_wfd($ssl, fileno(STDOUT));

    Net::SSLeay::use_RSAPrivateKey_file ($ssl, 'plain-rsa.pem',
                                         Net::SSLeay::FILETYPE_PEM);
    die_if_ssl_error("private key");
    Net::SSLeay::use_certificate_file ($ssl, 'plain-cert.pem',
                                       Net::SSLeay::FILETYPE_PEM);
    die_if_ssl_error("certificate");

    Net::SSLeay::accept($ssl) and die_if_ssl_err("ssl accept: $!");
    print "Cipher `" . Net::SSLeay::get_cipher($ssl) . "'\n";

    $got = Net::SSLeay::read($ssl);
    die_if_ssl_error("ssl read");
    print "Got `$got' (" . length ($got) . " chars)\n";

    Net::SSLeay::write ($ssl, uc($got)) or die "write: $!";
    die_if_ssl_error("ssl write");

    Net::SSLeay::free ($ssl);         # Tear down the connection
    Net::SSLeay::CTX_free ($ctx);
    close LOG;

There are also a number of example/test programs in the examples directory:

    sslecho.pl   -  A simple server, not unlike the one above
    minicli.pl   -  Implements a client using low level SSLeay routines
    sslcat.pl    -  Demonstrates using high level sslcat utility function
    get_page.pl  -  Is a utility for getting html pages from secure servers
    callback.pl  -  Demonstrates certificate verification and callback usage
    stdio_bulk.pl       - Does SSL over Unix pipes
    ssl-inetd-serv.pl   - SSL server that can be invoked from inetd.conf
    httpd-proxy-snif.pl - Utility that allows you to see how a browser
                          sends https request to given server and what reply
                          it gets back (very educative :-)
    makecert.pl  -  Creates a self signed cert (does not use this module)

=head1 LIMITATIONS

C<Net::SSLeay::read()> uses an internal buffer of 32KB, thus no single read
will return more. In practice one read returns much less, usually
as much as fits in one network packet. To work around this,
you should use a loop like this:

    $reply = '';
    while ($got = Net::SSLeay::read($ssl)) {
        last if print_errs('SSL_read');
        $reply .= $got;
    }

Although there is no built-in limit in C<Net::SSLeay::write()>, the network
packet size limitation applies here as well, thus use:

    $written = 0;

    while ($written < length($message)) {
        $written += Net::SSLeay::write($ssl, substr($message, $written));
        last if print_errs('SSL_write');
    }

Or alternatively you can just use the following convenience functions:

    Net::SSLeay::ssl_write_all($ssl, $message) or die "ssl write failure";
    $got = Net::SSLeay::ssl_read_all($ssl) or die "ssl read failure";

=head1 KNOWN BUGS AND CAVEATS

Autoloader emits a

    Argument "xxx" isn't numeric in entersub at blib/lib/Net/SSLeay.pm'

warning if die_if_ssl_error is made autoloadable. If you figure out why,
drop me a line.

Callback set using C<SSL_set_verify()> does not appear to work. This may
well be an openssl problem (e.g. see C<ssl/ssl_lib.c> line 1029). Try using
C<SSL_CTX_set_verify()> instead and do not be surprised if even this stops
working in future versions.

Callback and certificate verification stuff is generally too little tested.

Random numbers are not initialized randomly enough, especially if you
do not have C</dev/random> and/or C</dev/urandom> (such as in Solaris
platforms - but I've been suggested that cryptorand daemon from the SUNski
package solves this). In this case you should investigate third party
software that can emulate these devices, e.g. by way of a named pipe
to some program.

Another gotcha with random number initialization is randomness
depletion. This phenomenon, which has been extensively discussed in
OpenSSL, Apache-SSL, and Apache-mod_ssl forums, can cause your
script to block if you use C</dev/random> or to operate insecurely
if you use C</dev/urandom>. What happens is that when too much
randomness is drawn from the operating system's randomness pool
then randomness can temporarily be unavailable. C</dev/random> solves
this problem by waiting until enough randomness can be gathered - and
this can take a long time since blocking reduces activity in the
machine and less activity provides less random events: a vicious circle.
C</dev/urandom> solves this dilemma more pragmatically by simply returning
predictable "random" numbers. SomeC< /dev/urandom> emulation software
however actually seems to implement C</dev/random> semantics. Caveat emptor.

I've been pointed to two such daemons by Mik Firestone <mik@@speed.stdio._com>
who has used them on Solaris 8: 

=over

=item 1

Entropy Gathering Daemon (EGD) at L<http://www.lothar.com/tech/crypto/>

=item 2

Pseudo-random number generating daemon (PRNGD) at
L<http://www.aet.tu-cottbus.de/personen/jaenicke/postfix_tls/prngd.html>

=back

If you are using the low level API functions to communicate with other
SSL implementations, you would do well to call

    Net::SSLeay::CTX_set_options($ctx, &Net::SSLeay::OP_ALL)
         and die_if_ssl_error("ssl ctx set options");

to cope with some well know bugs in some other SSL
implementations. The high level API functions always set all known
compatibility options.

Sometimes C<sslcat()> (and the high level HTTPS functions that build on it)
is too fast in signaling the EOF to legacy HTTPS servers. This causes
the server to return empty page. To work around this problem you can
set the global variable

    $Net::SSLeay::slowly = 1;   # Add sleep so broken servers can keep up

HTTP/1.1 is not supported. Specifically this module does not know to
issue or serve multiple http requests per connection. This is a serious
shortcoming, but using the SSL session cache on your server helps to
alleviate the CPU load somewhat.

As of version 1.09 many newer OpenSSL auxiliary functions were
added (from C<REM_AUTOMATICALLY_GENERATED_1_09> onwards in C<SSLeay.xs>).
Unfortunately I have not had any opportunity to test these. Some of
them are trivial enough that I believe they "just work", but others
have rather complex interfaces with function pointers and all. In these
cases you should proceed wit great caution.

This module defaults to using OpenSSL automatic protocol negotiation
code for automatically detecting the version of the SSL protocol
that the other end talks. With most web servers this works just
fine, but once in a while I get complaints from people that the module
does not work with some web servers. Usually this can be solved
by explicitly setting the protocol version, e.g.

   $Net::SSLeay::ssl_version = 2;  # Insist on SSLv2
   $Net::SSLeay::ssl_version = 3;  # Insist on SSLv3
   $Net::SSLeay::ssl_version = 10; # Insist on TLSv1

Although the autonegotiation is nice to have, the SSL standards
do not formally specify any such mechanism. Most of the world has
accepted the SSLeay/OpenSSL way of doing it as the de facto standard. But
for the few that think differently, you have to explicitly speak
the correct version. This is not really a bug, but rather a deficiency
in the standards. If a site refuses to respond or sends back some
nonsensical error codes (at the SSL handshake level), try this option
before mailing me.

The high level API returns the certificate of the peer, thus allowing
one to check what certificate was supplied. However, you will only be
able to check the certificate after the fact, i.e. you already sent
your form data by the time you find out that you did not trust them,
oops.

So, while being able to know the certificate after the fact is surely
useful, the security minded would still choose to do the connection
and certificate verification first and only then exchange data
with the site. Currently none of the high level API functions do
this, thus you would have to program it using the low level API. A
good place to start is to see how the C<Net::SSLeay::http_cat()> function
is implemented.

The high level API functions use a global file handle C<SSLCAT_S>
internally. This really should not be a problem because there is no
way to interleave the high level API functions, unless you use threads
(but threads are not very well supported in perl anyway (as of version
5.6.1). However, you may run into problems if you call undocumented
internal functions in an interleaved fashion.

=head1 DIAGNOSTICS

=over

=item Random number generator not seeded!!!

B<(W)> This warning indicates that C<randomize()> was not able to read
C</dev/random> or C</dev/urandom>, possibly because your system does not
have them or they are differently named. You can still use SSL, but
the encryption will not be as strong.

=item open_tcp_connection: destination host not found:`server' (port 123) ($!)

Name lookup for host named C<server> failed.

=item open_tcp_connection: failed `server', 123 ($!)

The name was resolved, but establising the TCP connection failed.

=item msg 123: 1 - error:140770F8:SSL routines:SSL23_GET_SERVER_HELLO:unknown proto

SSLeay error string. The first number (123) is the PID, the second number
(1) indicates the position of the error message in SSLeay error stack.
You often see a pile of these messages as errors cascade.

=item msg 123: 1 - error:02001002::lib(2) :func(1) :reason(2)

The same as above, but you didn't call load_error_strings() so SSLeay
couldn't verbosely explain the error. You can still find out what it
means with this command:

    /usr/local/ssl/bin/ssleay errstr 02001002

=item Password is being asked for private key

This is normal behaviour if your private key is encrypted. Either
you have to supply the password or you have to use an unencrypted
private key. Scan OpenSSL.org for the FAQ that explains how to
do this (or just study examples/makecert.pl which is used
during C<make test> to do just that).

=back

=head1 BUGS AND SUPPORT

Please report any bugs or feature requests to
C<bug-Net-SSLeay at rt.cpan.org>, or through the web interface at
L<http://rt.cpan.org/Public/Dist/Display.html?Name=Net-SSLeay>.
I will be notified, and then you'll automatically be notified of progress on
your bug as I make changes.

Subversion access to the latest source code etc can be obtained at
L<http://alioth.debian.org/projects/net-ssleay>

The developer mailing list (for people interested in contributing
to the source code) can be found at
L<http://lists.alioth.debian.org/mailman/listinfo/net-ssleay-devel>

You can find documentation for this module with the C<perldoc> command.

    perldoc Net::SSLeay

You can also look for information at:

=over 4

=item * AnnoCPAN: Annotated CPAN documentation

L<http://annocpan.org/dist/Net-SSLeay>

=item * CPAN Ratings

L<http://cpanratings.perl.org/d/Net-SSLeay>

=item * Search CPAN

L<http://search.cpan.org/dist/Net-SSLeay>

=back

Commercial support for Net::SSLeay may be obtained from

   Symlabs (netssleay@symlabs.com)
   Tel: +351-214.222.630
   Fax: +351-214.222.637

=head1 AUTHOR

Maintained by Mike McCauley and Florian Ragwitz since November 2005

Originally written by Sampo Kellom�ki <sampo@symlabs.com>

=head1 COPYRIGHT

Copyright (c) 1996-2003 Sampo Kellom�ki <sampo@symlabs.com>

Copyright (C) 2005-2006 Florian Ragwitz <rafl@debian.org>

Copyright (C) 2005 Mike McCauley <mikem@open.com.au>

All Rights Reserved.

Distribution and use of this module is under the same terms as the
OpenSSL package itself (i.e. free, but mandatory attribution; NO
WARRANTY). Please consult LICENSE file in the root of the OpenSSL
distribution.

While the source distribution of this perl module does not contain
Eric's or OpenSSL's code, if you use this module you will use OpenSSL
library. Please give Eric and OpenSSL team credit (as required by
their licenses).

And remember, you, and nobody else but you, are responsible for
auditing this module and OpenSSL library for security problems,
backdoors, and general suitability for your application.

=head1 SEE ALSO

  Net::SSLeay::Handle                      - File handle interface
  ./examples                               - Example servers and a clients
  <http://www.openssl.org/>                - OpenSSL source, documentation, etc
  openssl-users-request@openssl.org        - General OpenSSL mailing list
  <http://www.ietf.org/rfc/rfc2246.txt>    - TLS 1.0 specification
  <http://www.w3c.org>                     - HTTP specifications
  <http://www.ietf.org/rfc/rfc2617.txt>    - How to send password
  <http://www.lothar.com/tech/crypto/>     - Entropy Gathering Daemon (EGD)
  <http://www.aet.tu-cottbus.de/personen/jaenicke/postfix_tls/prngd.html>
                           - pseudo-random number generating daemon (PRNGD)
  perl(1)
  perlref(1)
  perllol(1)
  perldoc ~openssl/doc/ssl/SSL_CTX_set_verify.pod

=cut

# ';

### Some methods that are macros in C

sub want_nothing { want(shift) == 1 }
sub want_read { want(shift) == 2 }
sub want_write { want(shift) == 3 }
sub want_X509_lookup { want(shift) == 4 }

###
### Open TCP stream to given host and port, looking up the details
### from system databases or DNS.
###

sub open_tcp_connection {
    my ($dest_serv, $port) = @_;
    my ($errs);
    
    $port = getservbyname($port, 'tcp') unless $port =~ /^\d+$/;
    my $dest_serv_ip = gethostbyname($dest_serv);
    unless (defined($dest_serv_ip)) {
	$errs = "$0 $$: open_tcp_connection: destination host not found:"
            . " `$dest_serv' (port $port) ($!)\n";
	warn $errs if $trace;
        return wantarray ? (0, $errs) : 0;
    }
    my $sin = sockaddr_in($port, $dest_serv_ip);
    
    warn "Opening connection to $dest_serv:$port (" .
	inet_ntoa($dest_serv_ip) . ")" if $trace>2;
    
    my $proto = getprotobyname('tcp');
    if (socket (SSLCAT_S, &PF_INET(), &SOCK_STREAM(), $proto)) {
        warn "next connect" if $trace>3;
        if (CORE::connect (SSLCAT_S, $sin)) {
            my $old_out = select (SSLCAT_S); $| = 1; select ($old_out);
            warn "connected to $dest_serv, $port" if $trace>3;
            return wantarray ? (1, undef) : 1; # Success
        }
    }
    $errs = "$0 $$: open_tcp_connection: failed `$dest_serv', $port ($!)\n";
    warn $errs if $trace;
    close SSLCAT_S;
    return wantarray ? (0, $errs) : 0; # Fail
}

### Open connection via standard web proxy, if one was defined
### using set_proxy().

sub open_proxy_tcp_connection {
    my ($dest_serv, $port) = @_;
    return open_tcp_connection($dest_serv, $port) if !$proxyhost;
    
    warn "Connect via proxy: $proxyhost:$proxyport" if $trace>2;
    my ($ret, $errs) = open_tcp_connection($proxyhost, $proxyport);
    return wantarray ? (0, $errs) : 0 if !$ret;  # Connection fail
    
    warn "Asking proxy to connect to $dest_serv:$port" if $trace>2;
    #print SSLCAT_S "CONNECT $dest_serv:$port HTTP/1.0$proxyauth$CRLF$CRLF";
    #my $line = <SSLCAT_S>;   # *** bug? Mixing stdio with syscall read?
    ($ret, $errs) =
	tcp_write_all("CONNECT $dest_serv:$port HTTP/1.0$proxyauth$CRLF$CRLF");
    return wantarray ? (0,$errs) : 0 if $errs;
    ($line, $errs) = tcp_read_until($CRLF . $CRLF, 1024);
    warn "Proxy response: $line" if $trace>2;
    return wantarray ? (0,$errs) : 0 if $errs;
    return wantarray ? (1,'') : 1;  # Success
}

###
### read and write helpers that block
###

sub debug_read {
    my ($replyr, $gotr) = @_;
    my $vm = $trace>2 && $linux_debug ?
	(split ' ', `cat /proc/$$/stat`)[22] : 'vm_unknown';
    warn "  got " . blength($$gotr) . ':'
	. blength($$replyr) . " bytes (VM=$vm).\n" if $trace == 3;
    warn "  got `$$gotr' (" . blength($$gotr) . ':'
	. blength($$replyr) . " bytes, VM=$vm)\n" if $trace>3;
}

sub ssl_read_all {
    my ($ssl,$how_much) = @_;
    $how_much = 2000000000 unless $how_much;
    my ($got, $errs);
    my $reply = '';

    while ($how_much > 0) {
        $got = Net::SSLeay::read($ssl,
                ($how_much > 32768) ? 32768 : $how_much
        );
        last if $errs = print_errs('SSL_read');
        $how_much -= blength($got);
        debug_read(\$reply, \$got) if $trace>1;
        last if $got eq '';  # EOF
        $reply .= $got;
    }

    return wantarray ? ($reply, $errs) : $reply;
}

sub tcp_read_all {
    my ($how_much) = @_;
    $how_much = 2000000000 unless $how_much;
    my ($n, $got, $errs);
    my $reply = '';

    my $bsize = 0x10000;
    while ($how_much > 0) {
	$n = sysread(SSLCAT_S,$got, (($bsize < $how_much) ? $bsize : $how_much));
	warn "Read error: $! ($n,$how_much)" unless defined $n;
	last if !$n;  # EOF
	$how_much -= $n;
	debug_read(\$reply, \$got) if $trace>1;
	$reply .= $got;
    }
    return wantarray ? ($reply, $errs) : $reply;
}

sub ssl_write_all {
    my $ssl = $_[0];    
    my ($data_ref, $errs);
    if (ref $_[1]) {
	$data_ref = $_[1];
    } else {
	$data_ref = \$_[1];
    }
    my ($wrote, $written, $to_write) = (0,0, blength($$data_ref));
    my $vm = $trace>2 && $linux_debug ?
	(split ' ', `cat /proc/$$/stat`)[22] : 'vm_unknown';
    warn "  write_all VM at entry=$vm\n" if $trace>2;
    while ($to_write) {
	#sleep 1; # *** DEBUG
	warn "partial `$$data_ref'\n" if $trace>3;
	$wrote = write_partial($ssl, $written, $to_write, $$data_ref);
	if (defined $wrote && ($wrote > 0)) {  # write_partial can return -1
	    $written += $wrote;
	    $to_write -= $wrote;
	} else {
	  if (defined $wrote) {
	    # check error conditions via SSL_get_error per man page
	    if ( my $sslerr = get_error($ssl, $wrote) ) {
	      my $errstr = ERR_error_string($sslerr);
	      my $errname = '';
	      SWITCH: {
		$sslerr == constant("ERROR_NONE") && do {
		  # according to map page SSL_get_error(3ssl):
		  #  The TLS/SSL I/O operation completed.  
		  #  This result code is returned if and only if ret > 0
                  # so if we received it here complain...
		  warn "ERROR_NONE unexpected with invalid return value!" 
		    if $trace;
		  $errname = "SSL_ERROR_NONE";
		};
		$sslerr == constant("ERROR_WANT_READ") && do {
		  # operation did not complete, call again later, so do not
		  # set errname and empty err_que since this is a known
		  # error that is expected but, we should continue to try
		  # writing the rest of our data with same io call and params.
		  warn "ERROR_WANT_READ (TLS/SSL Handshake, will continue)\n"
		    if $trace;
		  print_errs('SSL_write(want read)');
		  last SWITCH;
		};
		$sslerr == constant("ERROR_WANT_WRITE") && do {
		  # operation did not complete, call again later, so do not
		  # set errname and empty err_que since this is a known
		  # error that is expected but, we should continue to try
		  # writing the rest of our data with same io call and params.
		  warn "ERROR_WANT_WRITE (TLS/SSL Handshake, will continue)\n"
		    if $trace;
		  print_errs('SSL_write(want write)');
		  last SWITCH;
		};
		$sslerr == constant("ERROR_ZERO_RETURN") && do {
		  # valid protocol closure from other side, no longer able to
		  # write, since there is no longer a session...
		  warn "ERROR_ZERO_RETURN($wrote): TLS/SSLv3 Closure alert\n"
		    if $trace;
		  $errname = "SSL_ERROR_ZERO_RETURN";
		  last SWITCH;
		};
		$sslerr == constant("ERROR_SSL") && do {
		  # library/protocol error
		  warn "ERROR_SSL($wrote): Library/Protocol error occured\n"
		    if $trace;
		  $errname = "SSL_ERROR_SSL";
		  last SWITCH;
		};
		$sslerr == constant("ERROR_WANT_CONNECT") && do {
		  # according to man page, should never happen on call to
		  # SSL_write, so complain, but handle as known error type
		  warn "ERROR_WANT_CONNECT: Unexpected error for SSL_write\n"
		    if $trace;
		  $errname = "SSL_ERROR_WANT_CONNECT";
		  last SWITCH;
		};
		$sslerr == constant("ERROR_WANT_ACCEPT") && do { 
		  # according to man page, should never happen on call to
		  # SSL_write, so complain, but handle as known error type
		  warn "ERROR_WANT_ACCEPT: Unexpected error for SSL_write\n"
		    if $trace;
		  $errname = "SSL_ERROR_WANT_ACCEPT";
		  last SWITCH;
		};
		$sslerr == constant("ERROR_WANT_X509_LOOKUP") && do {
		  # operation did not complete: waiting on call back,  
		  # call again later, so do not set errname and empty err_que
		  # since this is a known error that is expected but, we should
		  # continue to try writing the rest of our data with same io
		  # call parameter.
		  warn "ERROR_WANT_X509_LOOKUP: (Cert Callback asked for in ".
		    "SSL_write will contine)\n" if $trace;
		  print_errs('SSL_write(want x509');
		  last SWITCH;
		};
		$sslerr == constant("ERROR_SYSCALL") && do {
		  # some IO error occured. According to man page: 
		  # Check retval, ERR, fallback to errno
		  if ($wrote==0) { # EOF
		    warn "ERROR_SYSCALL($wrote): EOF violates protocol.\n"
		      if $trace;
		    $errname = "SSL_ERROR_SYSCALL(EOF)";
		  } else { # -1 underlying BIO error reported.
		    # check error que for details, don't set errname since we
		    # are directly appending to errs
		    my $chkerrs = print_errs('SSL_write (syscall)');
		    if ($chkerrs) { 
		      warn "ERROR_SYSCALL($wrote): Have errors\n" if $trace;
		      $errs .= "ssl_write_all $$: 1 - ERROR_SYSCALL($wrote,".
			"$sslerr,$errstr,$!)\n$chkerrs";
		    } else { # que was empty, use errno
		      warn "ERROR_SYSCALL($wrote): errno($!)\n" if $trace;
		      $errs .= "ssl_write_all $$: 1 - ERROR_SYSCALL($wrote,".
			"$sslerr) : $!\n";
		    }
		  }
		  last SWITCH;
		};
		warn "Unhandled val $sslerr from SSL_get_error(SSL,$wrote)\n"
		  if $trace;
		$errname = "SSL_ERROR_?($sslerr)";
	      } # end of SWITCH block
	      if ($errname) { # if we had an errname set add the error
		$errs .= "ssl_write_all $$: 1 - $errname($wrote,$sslerr,".
		  "$errstr,$!)\n";
	      }	      
	    } # endif on have SSL_get_error val
	  } # endif on $wrote defined
	} # endelse on $wrote > 0
	$vm = $trace>2 && $linux_debug ?
	    (split ' ', `cat /proc/$$/stat`)[22] : 'vm_unknown';
	warn "  written so far $wrote:$written bytes (VM=$vm)\n" if $trace>2;
	# append remaining errors in que and report if errs exist
	$errs .= print_errs('SSL_write');
	return (wantarray ? (undef, $errs) : undef) if $errs;
    }
    return wantarray ? ($written, $errs) : $written;
}

sub tcp_write_all {
    my ($data_ref, $errs);
    if (ref $_[0]) {
	$data_ref = $_[0];
    } else {
	$data_ref = \$_[0];
    }
    my ($wrote, $written, $to_write) = (0,0, blength($$data_ref));
    my $vm = $trace>2 && $linux_debug ?
	(split ' ', `cat /proc/$$/stat`)[22] : 'vm_unknown';
    warn "  write_all VM at entry=$vm to_write=$to_write\n" if $trace>2;
    while ($to_write) {
	warn "partial `$$data_ref'\n" if $trace>3;
	$wrote = syswrite(SSLCAT_S, $$data_ref, $to_write, $written);
	if (defined $wrote && ($wrote > 0)) {  # write_partial can return -1
	    $written += $wrote;
	    $to_write -= $wrote;
	} elsif (!defined($wrote)) {
	    warn "tcp_write_all: $!";
	    return (wantarray ? (undef, "$!") : undef);
	}
	$vm = $trace>2 && $linux_debug ?
	    (split ' ', `cat /proc/$$/stat`)[22] : 'vm_unknown';
	warn "  written so far $wrote:$written bytes (VM=$vm)\n" if $trace>2;
    }
    return wantarray ? ($written, '') : $written;
}

### from patch by Clinton Wong <clintdw@netcom.com>

# ssl_read_until($ssl [, $delimit [, $max_length]])
#  if $delimit missing, use $/ if it exists, otherwise use \n
#  read until delimiter reached, up to $max_length chars if defined

sub ssl_read_until ($;$$) {
    my ($ssl,$delim, $max_length) = @_;
    local $[;

    # guess the delim string if missing
    if ( ! defined $delim ) {           
      if ( defined $/ && length $/  ) { $delim = $/ }
      else { $delim = "\n" }      # Note: \n,$/ value depends on the platform
    }
    my $len_delim = length $delim;

    my ($got);
    my $reply = '';
    
    # If we have OpenSSL 0.9.6a or later, we can use SSL_peek to
    # speed things up.
    # N.B. 0.9.6a has security problems, so the support for
    #      anything earlier than 0.9.6e will be dropped soon.
    if (&Net::SSLeay::OPENSSL_VERSION_NUMBER >= 0x0090601f) {
	$max_length = 2000000000 unless (defined $max_length);
	my ($pending, $peek_length, $found, $done);
	while (blength($reply) < $max_length and !$done) {
	    #Block if necessary until we get some data
	    $got = Net::SSLeay::peek($ssl,1);
	    last if print_errs('SSL_peek');

	    $pending = Net::SSLeay::pending($ssl) + blength($reply);
	    $peek_length = ($pending > $max_length) ? $max_length : $pending;
	    $peek_length -= blength($reply);
	    $got = Net::SSLeay::peek($ssl, $peek_length);
	    last if print_errs('SSL_peek');
	    $peek_length = blength($got);
	    
	    #$found = index($got, $delim);  # Old and broken
	    
	    # the delimiter may be split across two gets, so we prepend
	    # a little from the last get onto this one before we check
	    # for a match
	    my $match;
	    if(blength($reply) >= blength($delim) - 1) {
		#if what we've read so far is greater or equal
		#in length of what we need to prepatch
		$match = substr $reply, blength($reply) - blength($delim) + 1;
	    } else {
		$match = $reply;
	    }

	    $match .= $got;
	    $found = index($match, $delim);

	    if ($found > -1) {
		#$got = Net::SSLeay::read($ssl, $found+$len_delim);
		#read up to the end of the delimiter
		$got = Net::SSLeay::read($ssl,
					 $found + $len_delim
					 - ((blength $match) - (blength $got)));
		$done = 1;
	    } else {
		$got = Net::SSLeay::read($ssl, $peek_length);
		$done = 1 if ($peek_length == $max_length - blength($reply));
	    } 

	    last if print_errs('SSL_read');
	    debug_read(\$reply, \$got) if $trace>1;
	    last if $got eq '';
	    $reply .= $got;
	}
    } else {
	while (!defined $max_length || length $reply < $max_length) {
	    $got = Net::SSLeay::read($ssl,1);  # one by one
	    last if print_errs('SSL_read');
	    debug_read(\$reply, \$got) if $trace>1;
	    last if $got eq '';
	    $reply .= $got;
	    last if $len_delim
		&& substr($reply, blength($reply)-$len_delim) eq $delim;
	}
    }
    return $reply;
}

sub tcp_read_until {
    my ($delim, $max_length) = @_;
    local $[;

    # guess the delim string if missing
    if ( ! defined $delim ) {           
      if ( defined $/ && length $/  ) { $delim = $/ }
      else { $delim = "\n" }      # Note: \n,$/ value depends on the platform
    }
    my $len_delim = length $delim;

    my ($n,$got);
    my $reply = '';
    
    while (!defined $max_length || length $reply < $max_length) {
	$n = sysread(SSLCAT_S, $got, 1);  # one by one
	warn "tcp_read_until: $!" if !defined $n;
	debug_read(\$reply, \$got) if $trace>1;
	last if !$n;  # EOF
	$reply .= $got;
	last if $len_delim
	    && substr($reply, blength($reply)-$len_delim) eq $delim;
    }
    return $reply;
}

# ssl_read_CRLF($ssl [, $max_length])
sub ssl_read_CRLF ($;$) { ssl_read_until($_[0], $CRLF, $_[1]) }
sub tcp_read_CRLF { tcp_read_until($CRLF, $_[0]) }

# ssl_write_CRLF($ssl, $message) writes $message and appends CRLF
sub ssl_write_CRLF ($$) { 
  # the next line uses less memory but might use more network packets
  return ssl_write_all($_[0], $_[1]) + ssl_write_all($_[0], $CRLF);

  # the next few lines do the same thing at the expense of memory, with
  # the chance that it will use less packets, since CRLF is in the original
  # message and won't be sent separately.

  #my $data_ref;
  #if (ref $_[1]) { $data_ref = $_[1] }
  # else { $data_ref = \$_[1] }
  #my $message = $$data_ref . $CRLF;
  #return ssl_write_all($_[0], \$message);
}

sub tcp_write_CRLF { 
  # the next line uses less memory but might use more network packets
  return tcp_write_all($_[0]) + tcp_write_all($CRLF);

  # the next few lines do the same thing at the expense of memory, with
  # the chance that it will use less packets, since CRLF is in the original
  # message and won't be sent separately.

  #my $data_ref;
  #if (ref $_[1]) { $data_ref = $_[1] }
  # else { $data_ref = \$_[1] }
  #my $message = $$data_ref . $CRLF;
  #return tcp_write_all($_[0], \$message);
}

### Quickly print out with whom we're talking

sub dump_peer_certificate ($) {
    my ($ssl) = @_;
    my $cert = get_peer_certificate($ssl);
    return if print_errs('get_peer_certificate');
    print "no cert defined\n" if !defined($cert);
    # Cipher=NONE with empty cert fix
    if (!defined($cert) || ($cert == 0)) {
	warn "cert = `$cert'\n" if $trace;
	return "Subject Name: undefined\nIssuer  Name: undefined\n";
    } else {
	my $x = 'Subject Name: '
	    . X509_NAME_oneline(X509_get_subject_name($cert)) . "\n"
		. 'Issuer  Name: '
		    . X509_NAME_oneline(X509_get_issuer_name($cert))  . "\n";
	Net::SSLeay::X509_free($cert);
	return $x;
    }
}

### Arrange some randomness for eay PRNG

sub randomize (;$$) {
    my ($rn_seed_file, $seed, $egd_path) = @_;
    my $rnsf = defined($rn_seed_file) && -r $rn_seed_file;

	$egd_path = '';
    $egd_path = $ENV{'EGD_PATH'} if $ENV{'EGD_PATH'};
    
    RAND_seed(rand() + $$);  # Stir it with time and pid
    
    unless ($rnsf || -r $Net::SSLeay::random_device || $seed || -S $egd_path) {
	warn "Random number generator not seeded!!!" if $trace;
    }
    
    RAND_load_file($rn_seed_file, -s _) if $rnsf;
    RAND_seed($seed) if $seed;
    RAND_seed($ENV{RND_SEED}) if $ENV{RND_SEED};
    RAND_egd($egd_path) if -e $egd_path && -S _;
    RAND_load_file($Net::SSLeay::random_device, $Net::SSLeay::how_random/8)
	if -r $Net::SSLeay::random_device;
}

sub new_x_ctx {
    if    ($ssl_version == 2)  { $ctx = CTX_v2_new(); }
    elsif ($ssl_version == 3)  { $ctx = CTX_v3_new(); }
    elsif ($ssl_version == 10) { $ctx = CTX_tlsv1_new(); }
    else                       { $ctx = CTX_new(); }
    return $ctx;
}

###
### Basic request - response primitive (don't use for https)
###

sub sslcat { # address, port, message, $crt, $key --> reply / (reply,errs,cert)
    my ($dest_serv, $port, $out_message, $crt_path, $key_path) = @_;
    my ($ctx, $ssl, $got, $errs, $written);
    
    ($got, $errs) = open_proxy_tcp_connection($dest_serv, $port);
    return (wantarray ? (undef, $errs) : undef) unless $got;
    
    ### Do SSL negotiation stuff
	    
    warn "Creating SSL $ssl_version context...\n" if $trace>2;
    load_error_strings();         # Some bloat, but I'm after ease of use
    SSLeay_add_ssl_algorithms();  # and debuggability.
    randomize();
    
    $ctx = new_x_ctx();
    goto cleanup2 if $errs = print_errs('CTX_new') or !$ctx;

    CTX_set_options($ctx, &OP_ALL);
    goto cleanup2 if $errs = print_errs('CTX_set_options');

    warn "Cert `$crt_path' given without key" if $crt_path && !$key_path;
    set_cert_and_key($ctx, $crt_path, $key_path) if $crt_path;
    
    warn "Creating SSL connection (context was '$ctx')...\n" if $trace>2;
    $ssl = new($ctx);
    goto cleanup if $errs = print_errs('SSL_new') or !$ssl;
    
    warn "Setting fd (ctx $ctx, con $ssl)...\n" if $trace>2;
    set_fd($ssl, fileno(SSLCAT_S));
    goto cleanup if $errs = print_errs('set_fd');
    
    warn "Entering SSL negotiation phase...\n" if $trace>2;

    if ($trace>2) {
	my $i = 0;
	my $p = '';
	my $cipher_list = 'Cipher list: ';
	$p=Net::SSLeay::get_cipher_list($ssl,$i);
	$cipher_list .= $p if $p;
	do {
	    $i++;
	    $cipher_list .= ', ' . $p if $p;
	    $p=Net::SSLeay::get_cipher_list($ssl,$i);
	} while $p;
	$cipher_list .= '\n';
	warn $cipher_list;
    }
    
    $got = Net::SSLeay::connect($ssl);
    warn "SSLeay connect returned $got\n" if $trace>2;
    goto cleanup if $errs = print_errs('SSL_connect');
    
    my $server_cert = get_peer_certificate($ssl);
    print_errs('get_peer_certificate');
    if ($trace>1) {	    
	warn "Cipher `" . get_cipher($ssl) . "'\n";
	print_errs('get_ciper');
	warn dump_peer_certificate($ssl);
    }
    
    ### Connected. Exchange some data (doing repeated tries if necessary).
        
    warn "sslcat $$: sending " . blength($out_message) . " bytes...\n"
	if $trace==3;
    warn "sslcat $$: sending `$out_message' (" . blength($out_message)
	. " bytes)...\n" if $trace>3;
    ($written, $errs) = ssl_write_all($ssl, $out_message);
    goto cleanup unless $written;
    
    sleep $slowly if $slowly;  # Closing too soon can abort broken servers
    CORE::shutdown SSLCAT_S, 1;  # Half close --> No more output, send EOF to server
    
    warn "waiting for reply...\n" if $trace>2;
    ($got, $errs) = ssl_read_all($ssl);
    warn "Got " . blength($got) . " bytes.\n" if $trace==3;
    warn "Got `$got' (" . blength($got) . " bytes)\n" if $trace>3;

cleanup:	    
    free ($ssl);
    $errs .= print_errs('SSL_free');
cleanup2:
    CTX_free ($ctx);
    $errs .= print_errs('CTX_free');
    close SSLCAT_S;    
    return wantarray ? ($got, $errs, $server_cert) : $got;
}

sub tcpcat { # address, port, message, $crt, $key --> reply / (reply,errs,cert)
    my ($dest_serv, $port, $out_message) = @_;
    my ($got, $errs, $written);
    
    ($got, $errs) = open_proxy_tcp_connection($dest_serv, $port);
    return (wantarray ? (undef, $errs) : undef) unless $got;
    
    ### Connected. Exchange some data (doing repeated tries if necessary).
        
    warn "tcpcat $$: sending " . blength($out_message) . " bytes...\n"
	if $trace==3;
    warn "tcpcat $$: sending `$out_message' (" . blength($out_message)
	. " bytes)...\n" if $trace>3;
    ($written, $errs) = tcp_write_all($out_message);
    goto cleanup unless $written;
    
    sleep $slowly if $slowly;  # Closing too soon can abort broken servers
    CORE::shutdown SSLCAT_S, 1;  # Half close --> No more output, send EOF to server
    
    warn "waiting for reply...\n" if $trace>2;
    ($got, $errs) = tcp_read_all($ssl);
    warn "Got " . blength($got) . " bytes.\n" if $trace==3;
    warn "Got `$got' (" . blength($got) . " bytes)\n" if $trace>3;

cleanup:
    close SSLCAT_S;    
    return wantarray ? ($got, $errs) : $got;
}

sub tcpxcat {
    my ($usessl, $site, $port, $req, $crt_path, $key_path) = @_;
    if ($usessl) {
	return sslcat($site, $port, $req, $crt_path, $key_path);
    } else {
	return tcpcat($site, $port, $req);
    }
}

###
### Basic request - response primitive, this is different from sslcat
###                 because this does not shutdown the connection.
###

sub https_cat { # address, port, message --> returns reply / (reply,errs,cert)
    my ($dest_serv, $port, $out_message, $crt_path, $key_path) = @_;
    my ($ctx, $ssl, $got, $errs, $written);
    
    ($got, $errs) = open_proxy_tcp_connection($dest_serv, $port);
    return (wantarray ? (undef, $errs) : undef) unless $got;
	    
    ### Do SSL negotiation stuff
	    
    warn "Creating SSL $ssl_version context...\n" if $trace>2;
    load_error_strings();         # Some bloat, but I'm after ease of use
    SSLeay_add_ssl_algorithms();  # and debuggability.
    randomize();

    $ctx = new_x_ctx();
    goto cleanup2 if $errs = print_errs('CTX_new') or !$ctx;

    CTX_set_options($ctx, &OP_ALL);
    goto cleanup2 if $errs = print_errs('CTX_set_options');
    
    warn "Cert `$crt_path' given without key" if $crt_path && !$key_path;
    set_cert_and_key($ctx, $crt_path, $key_path) if $crt_path;
    
    warn "Creating SSL connection (context was '$ctx')...\n" if $trace>2;
    $ssl = new($ctx);
    goto cleanup if $errs = print_errs('SSL_new') or !$ssl;
    
    warn "Setting fd (ctx $ctx, con $ssl)...\n" if $trace>2;
    set_fd($ssl, fileno(SSLCAT_S));
    goto cleanup if $errs = print_errs('set_fd');
    
    warn "Entering SSL negotiation phase...\n" if $trace>2;
    
    if ($trace>2) {
	my $i = 0;
	my $p = '';
	my $cipher_list = 'Cipher list: ';
	$p=Net::SSLeay::get_cipher_list($ssl,$i);
	$cipher_list .= $p if $p;
	do {
	    $i++;
	    $cipher_list .= ', ' . $p if $p;
	    $p=Net::SSLeay::get_cipher_list($ssl,$i);
	} while $p;
	$cipher_list .= '\n';
	warn $cipher_list;
    }

    $got = Net::SSLeay::connect($ssl);
    warn "SSLeay connect failed" if $trace>2 && $got==0;
    goto cleanup if $errs = print_errs('SSL_connect');
    
    my $server_cert = get_peer_certificate($ssl);
    print_errs('get_peer_certificate');
    if ($trace>1) {	    
	warn "Cipher `" . get_cipher($ssl) . "'\n";
	print_errs('get_ciper');
	warn dump_peer_certificate($ssl);
    }
    
    ### Connected. Exchange some data (doing repeated tries if necessary).
        
    warn "https_cat $$: sending " . blength($out_message) . " bytes...\n"
	if $trace==3;
    warn "https_cat $$: sending `$out_message' (" . blength($out_message)
	. " bytes)...\n" if $trace>3;
    ($written, $errs) = ssl_write_all($ssl, $out_message);
    goto cleanup unless $written;
    
    warn "waiting for reply...\n" if $trace>2;
    ($got, $errs) = ssl_read_all($ssl);
    warn "Got " . blength($got) . " bytes.\n" if $trace==3;
    warn "Got `$got' (" . blength($got) . " bytes)\n" if $trace>3;

cleanup:
    free ($ssl);
    $errs .= print_errs('SSL_free');
cleanup2:
    CTX_free ($ctx);
    $errs .= print_errs('CTX_free');
    close SSLCAT_S;    
    return wantarray ? ($got, $errs, $server_cert) : $got;
}

sub http_cat { # address, port, message --> returns reply / (reply,errs,cert)
    my ($dest_serv, $port, $out_message) = @_;
    my ($got, $errs, $written);
    
    ($got, $errs) = open_proxy_tcp_connection($dest_serv, $port);
    return (wantarray ? (undef, $errs) : undef) unless $got;
	    
    ### Connected. Exchange some data (doing repeated tries if necessary).
        
    warn "http_cat $$: sending " . blength($out_message) . " bytes...\n"
	if $trace==3;
    warn "http_cat $$: sending `$out_message' (" . blength($out_message)
	. " bytes)...\n" if $trace>3;
    ($written, $errs) = tcp_write_all($out_message);
    goto cleanup unless $written;
    
    warn "waiting for reply...\n" if $trace>2;
    ($got, $errs) = tcp_read_all(200000);
    warn "Got " . blength($got) . " bytes.\n" if $trace==3;
    warn "Got `$got' (" . blength($got) . " bytes)\n" if $trace>3;

cleanup:
    close SSLCAT_S;    
    return wantarray ? ($got, $errs) : $got;
}

sub httpx_cat {
    my ($usessl, $site, $port, $req, $crt_path, $key_path) = @_;
    warn "httpx_cat: usessl=$usessl ($site:$port)" if $trace;
    if ($usessl) {
	return https_cat($site, $port, $req, $crt_path, $key_path);
    } else {
	return http_cat($site, $port, $req);
    }
}

###
### Easy set up of private key and certificate
###

sub set_cert_and_key ($$$) {
    my ($ctx, $cert_path, $key_path) = @_;    
    my $errs = '';
    # Following will ask password unless private key is not encrypted
    CTX_use_RSAPrivateKey_file ($ctx, $key_path, &FILETYPE_PEM);
    $errs .= print_errs("private key `$key_path' ($!)");
    CTX_use_certificate_file ($ctx, $cert_path, &FILETYPE_PEM);
    $errs .= print_errs("certificate `$cert_path' ($!)");
    return wantarray ? (undef, $errs) : ($errs eq '');
}

### Old deprecated API

sub set_server_cert_and_key ($$$) { &set_cert_and_key }

### Set up to use web proxy

sub set_proxy ($$;**) {
    ($proxyhost, $proxyport, $proxyuser, $proxypass) = @_;
    require MIME::Base64 if $proxyuser;
    $proxyauth = $CRLF . 'Proxy-authorization: Basic '
	. MIME::Base64::encode("$proxyuser:$proxypass", '')
	    if $proxyuser;
}

###
### Easy https manipulation routines
###

sub make_form {
    my (@fields) = @_;
    my $form;
    while (@fields) {
	my ($name, $data) = (shift(@fields), shift(@fields));
	$data =~ s/([^\w\-.\@\$ ])/sprintf("%%%2.2x",ord($1))/gse;
    	$data =~ tr[ ][+];
	$form .= "$name=$data&";
    }
    chop $form;
    return $form;
}

sub make_headers {
    my (@headers) = @_;
    my $headers;
    while (@headers) {
	my $header = shift(@headers);
	my $value = shift(@headers);
	$header =~ s/:$//;
	$value =~ s/\x0d?\x0a$//; # because we add it soon, see below
	$headers .= "$header: $value$CRLF";
    }
    return $headers;
}

sub do_httpx3 {
    my ($method, $usessl, $site, $port, $path, $headers,
	$content, $mime_type, $crt_path, $key_path) = @_;
    my ($response, $page, $h,$v);

    if ($content) {
	$mime_type = "application/x-www-form-urlencoded" unless $mime_type;
	my $len = blength($content);
	$content = "Content-Type: $mime_type$CRLF"
	    . "Content-Length: $len$CRLF$CRLF$content";
    } else {
	$content = "$CRLF$CRLF";
    }
    my $req = "$method $path HTTP/1.0$CRLF";
    unless (defined $headers && $headers =~ /^Host:/m) {
        $req .= "Host: $site";
        unless (($port == 80 && !$usessl) || ($port == 443 && $usessl)) {
            $req .= ":$port";
        }
        $req .= $CRLF;
	}
    $req .= (defined $headers ? $headers : '') . "Accept: */*$CRLF$content";    

    warn "do_httpx3($method,$usessl,$site:$port)" if $trace;
    my ($http, $errs, $server_cert)
	= httpx_cat($usessl, $site, $port, $req, $crt_path, $key_path);
    return (undef, "HTTP/1.0 900 NET OR SSL ERROR$CRLF$CRLF$errs") if $errs;
    
    $http = '' if !defined $http;
    ($headers, $page) = split /\s?\n\s?\n/, $http, 2;
    warn "headers >$headers< page >>$page<< http >>>$http<<<" if $trace>1;
    ($response, $headers) = split /\s?\n/, $headers, 2;
    return ($page, $response, $headers, $server_cert);
}

sub do_https3 { splice(@_,1,0) = 1; do_httpx3; }  # Legacy undocumented

### do_https2() is a legacy version in the sense that it is unable
### to return all instances of duplicate headers.

sub do_httpx2 {
    my ($page, $response, $headers, $server_cert) = &do_httpx3;
    X509_free($server_cert) if defined $server_cert;
    return ($page, $response,
	    map( { ($h,$v)=/^(\S+)\:\s*(.*)$/; (uc($h),$v); }
		split(/\s?\n/, $headers)
		)
	    );
}

sub do_https2 { splice(@_,1,0) = 1; do_httpx2; }  # Legacy undocumented

### Returns headers as a hash where multiple instances of same header
### are handled correctly.

sub do_httpx4 {
    my ($page, $response, $headers, $server_cert) = &do_httpx3;
    X509_free($server_cert) if defined $server_cert;
    my %hr = ();
    for my $hh (split /\s?\n/, $headers) {
	my ($h,$v)=/^(\S+)\:\s*(.*)$/;
	push @{$hr{uc($h)}}, $v;
    }
    return ($page, $response, \%hr);
}

sub do_https4 { splice(@_,1,0) = 1; do_httpx4; }  # Legacy undocumented

# https

sub get_https  { do_httpx2(GET  => 1, @_) }
sub post_https { do_httpx2(POST => 1, @_) }
sub put_https  { do_httpx2(PUT  => 1, @_) }
sub head_https { do_httpx2(HEAD => 1, @_) }

sub get_https3  { do_httpx3(GET  => 1, @_) }
sub post_https3 { do_httpx3(POST => 1, @_) }
sub put_https3  { do_httpx3(PUT  => 1, @_) }
sub head_https3 { do_httpx3(HEAD => 1, @_) }

sub get_https4  { do_httpx4(GET  => 1, @_) }
sub post_https4 { do_httpx4(POST => 1, @_) }
sub put_https4  { do_httpx4(PUT  => 1, @_) }
sub head_https4 { do_httpx4(HEAD => 1, @_) }

# http

sub get_http  { do_httpx2(GET  => 0, @_) }
sub post_http { do_httpx2(POST => 0, @_) }
sub put_http  { do_httpx2(PUT  => 0, @_) }
sub head_http { do_httpx2(HEAD => 0, @_) }

sub get_http3  { do_httpx3(GET  => 0, @_) }
sub post_http3 { do_httpx3(POST => 0, @_) }
sub put_http3  { do_httpx3(PUT  => 0, @_) }
sub head_http3 { do_httpx3(HEAD => 0, @_) }

sub get_http4  { do_httpx4(GET  => 0, @_) }
sub post_http4 { do_httpx4(POST => 0, @_) }
sub put_http4  { do_httpx4(PUT  => 0, @_) }
sub head_http4 { do_httpx4(HEAD => 0, @_) }

# Either https or http

sub get_httpx  { do_httpx2(GET  => @_) }
sub post_httpx { do_httpx2(POST => @_) }
sub put_httpx  { do_httpx2(PUT  => @_) }
sub head_httpx { do_httpx2(HEAD => @_) }

sub get_httpx3  { do_httpx3(GET  => @_) }
sub post_httpx3 { do_httpx3(POST => @_) }
sub put_httpx3  { do_httpx3(PUT  => @_) }
sub head_httpx3 { do_httpx3(HEAD => @_) }

sub get_httpx4  { do_httpx4(GET  => @_) }
sub post_httpx4 { do_httpx4(POST => @_) }
sub put_httpx4  { do_httpx4(PUT  => @_) }
sub head_httpx4 { do_httpx4(HEAD => @_) }

### Legacy, don't use
# ($page, $respone_or_err, %headers) = do_https(...);

sub do_https {
    my ($site, $port, $path, $method, $headers,
	$content, $mime_type, $crt_path, $key_path) = @_;

    do_https2($method, $site, $port, $path, $headers,
	     $content, $mime_type, $crt_path, $key_path);
}
 
1;
__END__
