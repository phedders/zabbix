update items set history=history/(24*3600);

update config set alert_history=alert_history/(24*3600);
update config set alarm_history=alarm_history/(24*3600);

alter table triggers add status int(4) DEFAULT '0' NOT NULL;
alter table triggers add value int(4) DEFAULT '0' NOT NULL;

alter table alerts add status int(4) DEFAULT '0' NOT NULL;
alter table alerts add retries int(4) DEFAULT '0' NOT NULL;
create index status_retries on alerts (status,retries);

alter table items add trapper_hosts varchar(255) DEFAULT '' NOT NULL;

update triggers set status=0 where istrue in (0,1,3);
update triggers set status=1 where istrue in (2);
update triggers set status=2 where istrue in (4);

update triggers set value=0 where istrue in (0);
update triggers set value=1 where istrue in (1);
update triggers set value=2 where istrue in (2,3,4);

alter table triggers drop istrue;

alter table alarms add value int(4) DEFAULT '0' NOT NULL;
update alarms set value=0 where istrue in (0);
update alarms set value=1 where istrue in (1);
update alarms set value=2 where istrue not in (0,1);

alter table services add algorithm int(1) DEFAULT '0' NOT NULL;

insert into items_template (itemtemplateid,description,key_,delay,value_type)
        values (69,'Host status','status', 60, 0);
insert into triggers_template (triggertemplateid,itemtemplateid,description,expression)
        values (69,69,'Server %s is unreachable','{:.last(0)}=2');

insert into users (userid,alias,name,surname,passwd) values (2,'guest','Default','User','d41d8cd98f00b204e9800998ecf8427e');
insert into rights (rightid,userid,name,permission,id) values (3,2,'Default permission','R',0);

--
-- Table structure for table 'sessions'
--

CREATE TABLE sessions (
  sessionid             varchar(32)     NOT NULL DEFAULT '',
  userid                int(4)          NOT NULL DEFAULT '0',
  lastaccess            int(4)          NOT NULL DEFAULT '0',
  PRIMARY KEY (sessionid)
);