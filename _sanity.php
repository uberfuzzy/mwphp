<?php
$files = glob('*.php');

$res = array(
	'ok' => 0,
	'fail' => array(),
	);

$known = 'No syntax errors detected';
$known_len = strlen($known);

foreach( $files as $fn )
{
	$ret = @exec('php -l ' . $fn);
	if( substr($ret, 0, $known_len) == $known )
	{
		$res['ok']++;
	}
	else
	{
		var_dump($ret);
		$res['fail'][] = $fn;
	}
}

$files_count = count($files);
$ok_count = $res['ok'];
$fail_count = count($res['fail']);

print "\n";
print "ok={$ok_count}/{$files_count} (". number_format( ($ok_count/$files_count)*100 ,2) ."%)\n";
print "fail={$fail_count}/{$files_count} (". number_format( ($fail_count/$files_count)*100 ,2) ."%)\n";
if( $fail_count )
{
	foreach($res['fail'] as $fail)
	{
		print $fail . "\n";
	}
}