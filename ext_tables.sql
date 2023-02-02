CREATE TABLE tt_content
(
    tx_rsmoembed_url            varchar(1024) NOT NULL DEFAULT '',
    tx_rsmoembed_data           text          NOT NULL DEFAULT '',
    tx_rsmoembed_image_download smallint(1) unsigned NOT NULL DEFAULT '0',
    tx_rsmoembed_image          int(11) unsigned NOT NULL DEFAULT '0'
);
