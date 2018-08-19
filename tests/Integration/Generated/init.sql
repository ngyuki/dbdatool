DROP TABLE IF EXISTS t;

CREATE TABLE t (
  sidea DOUBLE,
  sideb DOUBLE,
  sidec DOUBLE AS (SQRT(sidea * sidea + sideb * sideb))
);
