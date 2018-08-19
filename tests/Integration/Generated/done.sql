DROP TABLE IF EXISTS t;

CREATE TABLE t (
  sidea DOUBLE,
  sideb DOUBLE,
  sidec DOUBLE AS (sidea + sideb),
  sided DOUBLE AS (sidea * sideb)
);
