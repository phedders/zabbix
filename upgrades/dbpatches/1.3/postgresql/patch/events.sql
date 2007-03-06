CREATE TABLE events (
	eventid		bigint	DEFAULT '0'	NOT NULL,
	triggerid	bigint	DEFAULT '0'	NOT NULL,
	clock		integer	DEFAULT '0'	NOT NULL,
	value		integer	DEFAULT '0'	NOT NULL,
	acknowledged	integer		DEFAULT '0'	NOT NULL,
	PRIMARY KEY (eventid)
);
CREATE INDEX events_1 on events (triggerid,clock);
CREATE INDEX events_2 on events (clock);

insert into events select * from alarms;
drop table alarms;