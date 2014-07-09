DROP INDEX org_name ON organization ;
ALTER TABLE organization MODIFY org_name TEXT ; 
ALTER TABLE organization MODIFY org_brief_name TEXT ; 
CREATE INDEX org_name ON organization (org_name(20) , org_brief_name(20)); 
