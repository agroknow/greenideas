<?php
function oai_error($code, $argument = '', $value = '')
{
	global $request;
	global $request_err;

	switch ($code) {
		case 'badArgument' :
			$text = "The argument '$argument' (value='$value') included in the request is not valid.";
			break;

		case 'badGranularity' :
			$text = "The value '$value' of the argument '$argument' is not valid.";
			$code = 'badArgument';
			break;

		case 'badResumptionToken' :
			$text = "The resumptionToken '$value' does not exist or has already expired.";
			break;

		case 'badRequestMethod' :
			$text = "The request method '$argument' is unknown.";
			$code = 'badVerb';
			break;

		case 'badVerb' :
			$text = "The verb '$argument' provided in the request is illegal.";
			break;

		case 'cannotDisseminateFormat' :
			$text = "The metadata format '$value' given by $argument is not supported by this repository.";
			break;

		case 'exclusiveArgument' :
			$text = 'The usage of resumptionToken as an argument allows no other arguments.';
			$code = 'badArgument';
			break;

		case 'idDoesNotExist' :
			$text = "The value '$value' of the identifier is illegal for this repository.";
			if (!is_valid_uri($value)) {
				$code = 'badArgument';
			}
			break;

		case 'missingArgument' :
			$text = "The required argument '$argument' is missing in the request.";
			$code = 'badArgument';
			break;

		case 'noRecordsMatch' :
			$text = 'The combination of the given values results in an empty list.';
			break;

		case 'noMetadataFormats' :
			$text = 'There are no metadata formats available for the specified item.';
			break;

		case 'noVerb' :
			$text = 'The request does not provide any verb.';
			$code = 'badVerb';
			break;

		case 'noSetHierarchy' :
			$text = 'This repository does not support sets.';
			break;

		case 'sameArgument' :
			$text = 'Do not use them same argument more than once.';
			$code = 'badArgument';
			break;

		case 'sameVerb' :
			$text = 'Do not use verb more than once.';
			$code = 'badVerb';
			break;

		default:
			$text = "Unknown error: code: '$code', argument: '$argument', value: '$value'";
			$code = 'badArgument';
	}

	if ($code == 'badVerb' || $code == 'badArgument') {
		$request = $request_err;
	}
	$error = ' <error code="'.xmlstr($code, 'iso8859-1', false).'">'.xmlstr($text, 'iso8859-1', false)."</error>\n";
	return $error;
}

function xmlstr($string, $charset = 'iso8859-1', $xmlescaped = 'false')
{
	$xmlstr = stripslashes(trim($string));
	// just remove invalid characters
	$pattern ="/[\x-\x8\xb-\xc\xe-\x1f]/";
    $xmlstr = preg_replace($pattern, '', $xmlstr);

	// escape only if string is not escaped
	if (!$xmlescaped) {
		$xmlstr = htmlspecialchars($xmlstr, ENT_QUOTES);
	}

	if ($charset != "utf-8") {
		$xmlstr = utf8_encode($xmlstr);
	}

	return $xmlstr;
}

function oai_close()
{
	global $compress;

	echo "</OAI-PMH>\n";

}

function date2UTCdatestamp($date)
{
	global $granularity;

	if ($date == '') return '';
	
	switch ($granularity) {

		case 'YYYY-MM-DDThh:mm:ssZ':
			// we assume common date ("YYYY-MM-DD") 
			// or datetime format ("YYYY-MM-DD hh:mm:ss")
			// or datetime format with timezone YYYY-MM-DD hh:mm:ss+02
			// or datetime format with GMT timezone YYYY-MM-DD hh:mm:ssZ
			// or datetime format with timezone YYYY-MM-DDThh:mm:ssZ
			// or datetime format with microseconds and
			//             with timezone YYYY-MM-DD hh:mm:ss.xxx+02
			// with all variations as above
			// in the database
			// 
			if (strstr($date, ' ') || strstr($date, 'T')) {
				$checkstr = '/([0-9]{4})(-)([0-9]{1,2})(-)([0-9]{1,2})([T ])([0-9]{2})(:)([0-9]{2})(:)([0-9]{2})(\.?)(\d*)([Z+-]{0,1})([0-9]{0,2})$/';
				$val = preg_match($checkstr, $date, $matches);
				if (!$val) {
					// show that we have an error
					return "0000-00-00T00:00:00Z";
				}
				// date is datetime format
				/*
				 * $matches for "2005-05-26 09:30:51.123+02"
				 *	[0] => 2005-05-26 09:30:51+02
				 *	[1] => 2005
				 *	[2] => -
				 *	[3] => 05
				 *	[4] => -
				 *	[5] => 26
				 *	[6] =>
				 *	[7] => 09
				 *	[8] => :
				 *	[9] => 30
				 *	[10] => :
				 *	[11] => 51
				 *	[12] => .
				 *	[13] => 123
				 *	[14] => +
				 *	[15] => 02
				 */
				if ($matches[14] == '+' || $matches[14] == '-') {
					// timezone is given
					// format ("YYYY-MM-DD hh:mm:ss+01")
					$tz = $matches[15];
					if ($tz != '') {
						//$timestamp = mktime($h, $min, $sec, $m, $d, $y);
						$timestamp = mktime($matches[7], $matches[9], $matches[11],
											$matches[3], $matches[5], $matches[1]);
						// add, subtract timezone offset to get GMT
						// 3600 sec = 1 h
						if ($matches[14] == '-') {
							// we are before GMT, thus we need to add
							$timestamp += (int) $tz * 3600; 
						} else {
							// we are after GMT, thus we need to subtract
							$timestamp -= (int) $tz * 3600; 
						}							
						return strftime("%Y-%m-%dT%H:%M:%SZ", $timestamp);
					}
				} elseif ($matches[14] == 'Z') {
					return str_replace(' ', 'T', $date);
				}				
				return str_replace(' ', 'T', $date).'Z';
			} else {
				// date is date format
				// granularity 'YYYY-MM-DD' should be used...
				return $date.'T00:00:00Z';
			}
			break;

		case 'YYYY-MM-DD':
			if (strstr($date, ' ')) {
				// date is datetime format
				list($date, $time) = explode(" ", $date);
				return $date;
			} else {
				return $date;
			}
			break;

		default: die("Unknown granularity!");
	}
}
function xmlformat($record, $element, $attr = '', $indent = 0)
{
	$charset="utf-8";
	global $xmlescaped;
		
	if ($attr != '') {
		$attr = ' '.$attr;
	}
	
	$str = '';
	if (is_array($record)) {
		foreach  ($record as $val) {
			$str .= str_pad('', $indent).'<'.$element.$attr.'>'.xmlstr($val, $charset, $xmlescaped).'</'.$element.">\n";
		}
		return $str;
	} elseif ($record != '') {
		return str_pad('', $indent).'<'.$element.$attr.'>'.xmlstr($record, $charset, $xmlescaped).'</'.$element.">\n";
	} else {
		return '';
	}
}

function oai_exit()
{
	global $CONTENT_TYPE;
	global $xmlheader;
	global $request;
	global $errors;

	header($CONTENT_TYPE);
	echo $xmlheader;
	echo $request;
	echo $errors;

	oai_close();
	exit();
}


?>