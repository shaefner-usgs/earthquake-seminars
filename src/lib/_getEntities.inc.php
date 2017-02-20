<?php

/**
 * Convert all applicable characters to plain text or html entities
 */
function msWordEntities ($str, $style='plain') {
  // first 7 are Windows Extended ASCII codes;
  // next 7 are Extended ASCII codes;
  // last 7 are UTF-8 codes
  $search = array (
    chr(212), chr(213), chr(210), chr(211), chr(208), chr(209), chr(201),
    chr(145), chr(146), chr(147), chr(148), chr(150), chr(151), chr(133),
    chr(0xe2) . chr(0x80) . chr(0x98),
      chr(0xe2) . chr(0x80) . chr(0x99),
      chr(0xe2) . chr(0x80) . chr(0x9c),
      chr(0xe2) . chr(0x80) . chr(0x9d),
      chr(0xe2) . chr(0x80) . chr(0x93),
      chr(0xe2) . chr(0x80) . chr(0x94),
      chr(0xe2) . chr(0x80) . chr(0xa6)
  );

  if ($style == 'smart') { // Replace special chars w/ html entity equivalents
    $replace = array (
      '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8211;', '&#8212;', '&#8230;',
      '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8211;', '&#8212;', '&#8230;',
      '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8211;', '&#8212;', '&#8230;'
    );
  } else {
    $replace = array ( // Replace special chars w/ plain text equivalents
      "'", "'", '"', '"', '--', '---', '...',
      "'", "'", '"', '"', '--', '---', '...',
      "'", "'", '"', '"', '--', '---', '...'
    );
  }

  return str_ireplace($search, $replace, $str);
}

/**
 * Convert all applicable characters to XML entities
 */
function xmlEntities ($str) {
  $search = array(
    '&quot;','&amp;','&amp;','&lt;','&gt;','&nbsp;','&iexcl;', '&cent;',
    '&pound;','&curren;','&yen;','&brvbar;','&sect;','&uml;','&copy;','&ordf;',
    '&laquo;','&not;','&shy;','&reg;','&macr;','&deg;','&plusmn;','&sup2;',
    '&sup3;','&acute;','&micro;','&para;','&middot;','&cedil;','&sup1;',
    '&ordm;','&raquo;','&frac14;','&frac12;','&frac34;','&iquest;','&Agrave;',
    '&Aacute;','&Acirc;','&Atilde;','&Auml;','&Aring;','&AElig;','&Ccedil;',
    '&Egrave;','&Eacute;','&Ecirc;','&Euml;','&Igrave;','&Iacute;','&Icirc;',
    '&Iuml;','&ETH;','&Ntilde;','&Ograve;','&Oacute;','&Ocirc;','&Otilde;',
    '&Ouml;','&times;','&Oslash;','&Ugrave;','&Uacute;','&Ucirc;','&Uuml;',
    '&Yacute;','&THORN;','&szlig;','&agrave;','&aacute;','&acirc;','&atilde;',
    '&auml;','&aring;','&aelig;','&ccedil;','&egrave;','&eacute;','&ecirc;',
    '&euml;','&igrave;','&iacute;','&icirc;','&iuml;','&eth;','&ntilde;',
    '&ograve;','&oacute;','&ocirc;','&otilde;','&ouml;','&divide;','&oslash;',
    '&ugrave;','&uacute;','&ucirc;','&uuml;','&yacute;','&thorn;','&yuml;'
  );
  $replace = array(
    '&#34;','&#38;','&#38;','&#60;','&#62;','&#160;','&#161;','&#162;','&#163;',
    '&#164;','&#165;','&#166;','&#167;','&#168;','&#169;','&#170;','&#171;',
    '&#172;','&#173;','&#174;','&#175;','&#176;','&#177;','&#178;','&#179;',
    '&#180;','&#181;','&#182;','&#183;','&#184;','&#185;','&#186;','&#187;',
    '&#188;','&#189;','&#190;','&#191;','&#192;','&#193;','&#194;','&#195;',
    '&#196;','&#197;','&#198;','&#199;','&#200;','&#201;','&#202;','&#203;',
    '&#204;','&#205;','&#206;','&#207;','&#208;','&#209;','&#210;','&#211;',
    '&#212;','&#213;','&#214;','&#215;','&#216;','&#217;','&#218;','&#219;',
    '&#220;','&#221;','&#222;','&#223;','&#224;','&#225;','&#226;','&#227;',
    '&#228;','&#229;','&#230;','&#231;','&#232;','&#233;','&#234;','&#235;',
    '&#236;','&#237;','&#238;','&#239;','&#240;','&#241;','&#242;','&#243;',
    '&#244;','&#245;','&#246;','&#247;','&#248;','&#249;','&#250;','&#251;',
    '&#252;','&#253;','&#254;','&#255;'
  );

  $str = msWordEntities($str);
  $str = htmlentities($str);
  $str = str_ireplace($search, $replace, $str);

  return $str;
}
