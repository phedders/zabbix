alter table screens_items add	rowspan		int4	DEFAULT '0' NOT NULL;
alter table users add	autologout		int4	DEFAULT '900' NOT NULL;
alter table users add	lang			varchar(5)	DEFAULT 'en_gb' NOT NULL;

drop table escalation_rules;
drop table escalations;

--
-- Table structure for table 'escalations'
--

CREATE TABLE escalations (
  escalationid		serial,
  name			varchar(64)	DEFAULT '0' NOT NULL,
  dflt			int2		DEFAULT '0' NOT NULL,
  PRIMARY KEY (escalationid)
);

CREATE UNIQUE INDEX escalations_name on escalations (name);

--
-- Table structure for table 'escalation_rules'
--

CREATE TABLE escalation_rules (
  escalationruleid	serial,
  escalationid		int4		DEFAULT '0' NOT NULL,
  level			int4		DEFAULT '0' NOT NULL,
  period		varchar(100)	DEFAULT '1-7,00:00-23:59' NOT NULL,
  delay			int4		DEFAULT '0' NOT NULL,
  actiontype		int4		DEFAULT '0' NOT NULL,
  PRIMARY KEY (escalationruleid),
  FOREIGN KEY (escalationid) REFERENCES escalations
);