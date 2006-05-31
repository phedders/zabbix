alter table items add value_type int(4) DEFAULT '0' NOT NULL;
alter table items_template add value_type int(4) DEFAULT '0' NOT NULL;

alter table items modify lastvalue varchar(255) default null;
alter table items modify prevvalue varchar(255) default null;

alter table functions modify lastvalue varchar(255) default '0.0000' not null;

CREATE TABLE history_str (
  itemid int(4) DEFAULT '0' NOT NULL,
  clock int(4) DEFAULT '0' NOT NULL,
  value varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY (itemid,clock)
);

drop table groups;
drop table services_links;

CREATE TABLE services_links (
  linkid                int(4)          NOT NULL auto_increment,
  serviceupid           int(4)          DEFAULT '0' NOT NULL,
  servicedownid         int(4)          DEFAULT '0' NOT NULL,
  soft                  int(1)          DEFAULT '0' NOT NULL,
  PRIMARY KEY (linkid),
  KEY (serviceupid),
  KEY (servicedownid),
  UNIQUE (serviceupid,servicedownid)
);

CREATE TABLE rights (
  rightid               int(4)          NOT NULL auto_increment,
  userid                int(4)          DEFAULT '' NOT NULL,
  name                  char(255)       DEFAULT '' NOT NULL,
  permission            char(1)         DEFAULT '' NOT NULL,
  id                    int(4),
  PRIMARY KEY (rightid)
);

insert into rights (rightid,userid,name,permission,id) values (1,1,"Default permission","U",0);
insert into rights (rightid,userid,name,permission,id) values (2,1,"Default permission","A",0);


alter table users drop groupid;
alter table config drop password_required;

insert into items_template (itemtemplateid,description,key_,delay,value_type)
        values (65,'Host name','system[hostname]', 1800, 1);
insert into items_template (itemtemplateid,description,key_,delay,value_type)
        values (66,'Host information','system[uname]', 1800, 1);
insert into items_template (itemtemplateid,description,key_,delay,value_type)
        values (67,'Version of zabbix_agent(d) running','version[zabbix_agent]', 3600, 1);
insert into items_template (itemtemplateid,description,key_,delay,value_type)
        values (68,'WEB (HTTP) server is running','check_service[http]', 60, 0);insert into triggers_template (triggertemplateid,itemtemplateid,description,expression)
        values (68,68,'WEB (HTTP) server is down on %s','{:.last(0)}<1');