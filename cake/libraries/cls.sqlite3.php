<?php

class sqlite3
{
    function sqlite3_open($location)
    {
        $handle = new SQLite3($location);
        return $handle;
    }
    function sqlite3_query($dbhandle,$query)
    {
        $array['dbhandle'] = $dbhandle;
        $array['query'] = $query;
        $result = $dbhandle->query($query);
        return $result;
    }
    function sqlite3_exec($dbhandle,$query)
    {
        $array['dbhandle'] = $dbhandle;
        $array['query'] = $query;
        $dbhandle->exec($query);
        return true;
    }

    function sqlite3_fetch_array(&$result,$type)
    {
        #Get Columns
        $i = 0;
        while ($result->columnName($i))
        {
            $columns[ ] = $result->columnName($i);
            $i++;
        }
       
        $resx = $result->fetchArray(SQLITE3_ASSOC);
        return $resx;
    } 
}
