--
-- Table structure for table 'trends'
--

CREATE TABLE trends (
  itemid                int4            DEFAULT '0' NOT NULL,
  clock                 int4            DEFAULT '0' NOT NULL,
  num                   int2            DEFAULT '0' NOT NULL,
  value_min             float8          DEFAULT '0.0000' NOT NULL,
  value_avg             float8          DEFAULT '0.0000' NOT NULL,
  value_max             float8          DEFAULT '0.0000' NOT NULL,
  PRIMARY KEY (itemid,clock),
  FOREIGN KEY (itemid) REFERENCES items
);

update triggers set description=replace(description,'%s','{HOSTNAME}');
update actions set subject=replace(subject,'%s','{HOSTNAME}');
update actions set message=replace(message,'%s','{HOSTNAME}');

alter table sysmaps_links add  drawtype_off	int4		DEFAULT '0' NOT NULL;
alter table sysmaps_links add  color_off	varchar(32)	DEFAULT 'Black' NOT NULL;
alter table sysmaps_links add  drawtype_on	int4		DEFAULT '0' NOT NULL;
alter table sysmaps_links add  color_on		varchar(32)	DEFAULT 'Red' NOT NULL;