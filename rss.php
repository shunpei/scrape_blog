<?php
error_reporting(0);
ini_set( 'display_errors', 0 );

mb_internal_encoding('UTF-8');

define("BASEURL","http://www.athleteyell.jp");
define("INDEXURL","http://www.athleteyell.jp/yoshida_taku/information.html");

header("Content-Type: text/xml; charset=utf-8");
$context = stream_context_create(array('http' => array(
	'method' => 'GET',
	'header' => 'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)'
)));

function parseEntry($html)
{
	$r=array();
	if(preg_match('#<span class="date">([0-9]+)-([0-9]+)-([0-9]+) ([0-9]+):([0-9]+)</span>#msU',$html,$m))
		$r['date']=date(DATE_RFC2822,mktime($m[4],$m[5],$m[6],$m[2],$m[3],$m[1]) + 32400);
	
	if(preg_match('#<span class="left"><span class="category">ニュース</span>(.+)</span>#msU',$html,$m))
		$r['title']=trim(strip_tags($m[1]));
		
	if(preg_match('#<span class="theme">(テーマ：|)(.+)</span>#msU',$html,$m))
		$r['category']=trim(strip_tags($m[2]));
		
	if(preg_match('#</h3>(.+)</div>#msU',$html,$m))
		$r['description']=trim(strip_tags($m[1]));
	
	if(preg_match('#<a href="([^"]+)">記事URL</a>#msU',$html,$m))
		$r['url']=trim($m[1]);
	
	return $r;
}

$html=file_get_contents(INDEXURL,false,$context);

if(preg_match('#<h1><a href="(http://.+)">(.+)</h1>#msU',$html,$m))
{	$baseurl=$m[1];
	$title=trim(strip_tags($m[2]));
}
if(preg_match('#<h2>(.+)</h2>#msU',$html,$m))
	$desc=trim(strip_tags($m[1]));

$htmls=array();
$m=array();
preg_match_all('#<span class="left"><span class="category">ニュース</span><a href="(.+)">.+</a></span>#msU',$html,$m,PREG_SET_ORDER);
foreach($m as $i)
{	
	$html=file_get_contents(BASEURL.$i[1],false,$context);
	if($html)
	{
		$html.='<a href="'.BASEURL.$i[1].'">記事URL</a>';
		$htmls[]=$html;
	}
}


echo '<','?xml version="1.0" encoding="utf-8"?','>';
?>
<rss version="2.0">
  <channel>
    <title>吉田 拓（カヌー）×アスリート後援会代行・支援サイト【Athlete Yell（アスリートエール）】</title>
    <link>http://www.athleteyell.jp/yoshida_taku/</link>
    <description>吉田 拓（カヌー）×アスリート後援会代行・支援サイト【Athlete Yell（アスリートエール）】</description>
    <language>ja</language>
<?php
foreach($htmls as $html)
{	if($item=parseEntry($html))
	{	echo "<item>";
		echo "<title>",htmlspecialchars($item['title']),"</title>";
		echo "<category>ニュース</category>";
		echo "<description><![CDATA[",$item['description'],"]]></description>";
		echo "<link>",$item['url'],"</link>";
		echo "<pubDate>",$item['date'],"</pubDate>";
		echo "</item>\n";
	}
}
?>
  </channel>
</rss>
