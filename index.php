<?php
/**
 * Fetch content directly or from a dump file?
 */
//$json = file_get_contents('http://m.deredactie.be/client/mvc/contents?channel=vrtnieuws');
$json = file_get_contents('dumpfile.json');
$data = json_decode($json,true);

/**
 * Generate quick & dirty RSS feed
 */
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
foreach ( $data['rows'][0]['bundle']['content'] as $id => $details ) {
    print "
        <item>
            <guid>"         . "http://m.deredactie.be/#!/snippet/" . $details['id']                                 . "</guid>
            <link>"         . "http://m.deredactie.be/#!/snippet/" . $details['id']                                 . "</link>
            <title>"        . strip_tags(html_entity_decode($details['content'][0]['title'], ENT_QUOTES, "utf-8"))  . "</title>
            <description>"  . strip_tags(html_entity_decode($details['content'][0]['text'] , ENT_QUOTES, "utf-8"))  . "</description>
            <dc:creator>"   . $details['author']                                                                    . "</dc:creator>
            <pubDate>"      . date(DATE_RFC2822,strtotime( $details['publicationDate'] ))                           . "</pubDate>
        </item>\n";
}
print $rssEnd;
