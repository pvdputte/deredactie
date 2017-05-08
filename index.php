<?php
date_default_timezone_set('Europe/Brussels');

// Link to m.deredactie.be or an alternative?
$urlPrefix = "http://m.deredactie.be/#!/snippet/";
//$urlPrefix = "http://futtta.be/redactie/?channel=redactie&amp;url=http://csclient.vrt.be/client/mvc/contents/ContentBundle/";

// Fetch content directly or from a dump file?
//$json = file_get_contents('http://m.deredactie.be/client/mvc/contents?channel=vrtnieuws');
$json = file_get_contents('dumpfile.json');
$data = json_decode($json,true);

// Deduce our own array
$items = array();
foreach ( $data['rows'][0]['bundle']['content'] as $id => $details ) {
    $items[] = array(
        'iso8601date'   => $details['publicationDate'],
        'date'          => date( DATE_RFC2822,strtotime( $details['publicationDate'] ) ),
        'url'           => $urlPrefix . $details['id'],
        'title'         => str_replace('&', '&amp;', strip_tags(html_entity_decode( $details['content'][0]['title'], ENT_QUOTES, "utf-8" ) ) ),
        'desc'          => str_replace('&', '&amp;', strip_tags(html_entity_decode( $details['content'][0]['text'] , ENT_QUOTES, "utf-8" ) ) ),
        'creator'       => html_entity_decode( $details['author'], ENT_QUOTES, "utf-8" )
    );
}
usort($items, 'sortByIso8601dateDesc');

// Generate quick & dirty RSS feed
$copyright = "vrt © " . date('Y');
$pubDate = date(DATE_RFC2822);
$rssStart = <<<EOF
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel>
        <title>deredactie.be</title>
        <description>deredactie.be content with links to the mobile pages</description>
        <copyright>$copyright</copyright>
        <pubDate>$pubDate</pubDate>
        <lastBuildDate>$pubDate</lastBuildDate>
        <link>http://m.deredactie.be/</link>
        <image>
            <link>http://m.deredactie.be/</link>
            <title>deredactie.be</title>
            <url>http://deredactie.be/polopoly_fs/1.2026526!image/3280251518.png</url>
            <description>logo</description>
        </image>

EOF;
$rssEnd = <<<EOF

    </channel>
</rss>
EOF;

header('Content-Type: application/xml; charset=utf-8');
print $rssStart;
foreach ( $items as $index => $item ) {
    print "
        <item>
            <guid>"         . $item['url']       . "</guid>
            <link>"         . $item['url']       . "</link>
            <title>"        . $item['title']     . "</title>
            <description>"  . $item['desc']      . "</description>
            <dc:creator>"   . $item['creator']   . "</dc:creator>
            <pubDate>"      . $item['date']      . "</pubDate>
        </item>\n";
}
print $rssEnd;


// for usort
function sortByIso8601dateDesc($a, $b) {
    return strcmp( $b['iso8601date'], $a['iso8601date'] );
}
