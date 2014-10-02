-- Change recommendation from 0=no,1=yes
-- to: -2=no,2=yes,0=neutral,-1=yes with changes
-- Roughly based on gerrit review scale

UPDATE reviews
SET recommendation = -2
WHERE recommendation = 0;

UPDATE reviews
SET recommendation = 2
WHERE recommendation = 1;
