
        <quote Symbol="YHOO">
            <Date>2010-03-02</Date>
            <Open>15.87</Open>
            <High>15.96</High>
            <Low>15.67</Low>
            <Close>15.73</Close>
            <Volume>20101800</Volume>
            <Adj_Close>15.73</Adj_Close>
        </quote>
		
		that's 'from historical
		
		
		This is from quote:
		
		    <quote symbol="YHOO">
            <AverageDailyVolume>21783700</AverageDailyVolume>
            <Change>-0.63</Change>
            <DaysLow>33.97</DaysLow>
            <DaysHigh>34.81</DaysHigh>
            <YearLow>23.82</YearLow>
            <YearHigh>41.72</YearHigh>
            <MarketCapitalization>34.282B</MarketCapitalization>
			
            <LastTradePriceOnly>34.05</LastTradePriceOnly>
			
            <DaysRange>33.97 - 34.81</DaysRange>
            <Name>Yahoo! Inc.</Name>
            <Symbol>YHOO</Symbol>
            <Volume>21605808</Volume>
            <StockExchange>NasdaqNM</StockExchange>
        </quote>
		
create table historicquotes (
symbol varchar(10) not null,
thedate date not null,
closevalue integer not null,
PRIMARY KEY (symbol, thedate)
);

/* in actual fact: 
investments=# create table historicquotes (
symbol varchar(10) not null,
thedate date not null,
closevalue integer not null,
PRIMARY KEY (symbol, thedate)
);
NOTICE:  CREATE TABLE / PRIMARY KEY will create implicit index "historicquotes_pkey" for table "historicquotes"
CREATE TABLE
*/



create table stocknames (
symbol varchar(10) PRIMARY KEY REFERENCES historicquotes ON DELETE CASCADE,
name varchar(128) not null
);
/*
investments=# create table stocknames (
symbol varchar(10) PRIMARY KEY REFERENCES historicquotes ON DELETE CASCADE,
name varchar(128) not null 
);
NOTICE:  CREATE TABLE / PRIMARY KEY will create implicit index "stocknames_pkey" for table "stocknames"
ERROR:  number of referencing and referenced columns for foreign key disagree

investments-# \d historicquotes
         Table "public.historicquotes"
   Column   |         Type          | Modifiers 
------------+-----------------------+-----------
 symbol     | character varying(10) | not null
 thedate    | date                  | not null
 closevalue | integer               | not null
Indexes:
    "historicquotes_pkey" PRIMARY KEY, btree (symbol, thedate)
*/

create table stocknames (
symbol varchar(10) PRIMARY KEY,
name varchar(128) not null 
);
/*
investments=# create table stocknames (
investments(# symbol varchar(10) PRIMARY KEY,
investments(# name varchar(128) not null 
investments(# );
NOTICE:  CREATE TABLE / PRIMARY KEY will create implicit index "stocknames_pkey" for table "stocknames"
CREATE TABLE

investments-# \d stocknames    
          Table "public.stocknames"
 Column |          Type          | Modifiers 
--------+------------------------+-----------
 symbol | character varying(10)  | not null
 name   | character varying(128) | not null
Indexes:
    "stocknames_pkey" PRIMARY KEY, btree (symbol)


*/
investments=# CREATE USER php PASSWORD 'Susquehanna';
CREATE ROLE




INSERT INTO stocknames VALUES ('WOOF', 'Woof Incorporated');

ex:
UPDATE player SET playerteam = 7 WHERE playername = 'Peter Pan';
SELECT playername, playerteam FROM player WHERE playerid = '12345;



Disconnect
 investments=# \q














