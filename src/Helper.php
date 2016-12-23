<?php

namespace Helper;

class Helper 
{

    public function hello()
    {
        echo 'Hello world';
    }

    /**
    * Concatenates the passed arguments into one string
    * Dynamic number of arguments accepted of type string or numeric
    * !! Note: the 1st argument is the spacer
    * @return string
    * @throws Exception
    */
    public function glue()
    {
        $args = func_get_args();
        $string = '';
        $spacer = '';

        if( count($args) > 1) {
            $spacer = func_get_arg(0);
        }

        foreach ($args as $k => $val)
        {
            /** Force variable to string */
            try
            {
                $val = (string)$val;
            } catch (Exception $e)
            {
                throw new Exception('Argument no. ' . ++$k . ' could not be converted to string. Check the type of data.');
            }

            $string .= $val . $spacer;
        }

        return $string;
    }


    /**
    * Get date periods based on range ('Day', 'Week', 'Month')
    * Dependacey Carbon class
    * @param  string  $start | '20160113'
    * @param  string  $end   | '20160113'
    * @return array
    */
    public function getDatePeriod($start, $end, $range = 'Week')
    {
        $period = [];
        $range = ucfirst(strtolower($range));
        $end = $end.' 23:59:59';

        if (in_array($range, ['Day', 'Week', 'Month', 'Year'])) {
            $start = new \Carbon\Carbon($start);
            $end = new \Carbon\Carbon($end);

            while ($start <= $end) {
                $from = clone($start);
                $to = $start->{'endOf'.$range}();

                $period[] = [
                    'from' => $from->toDateTimeString(),
                    'to' => ${($end < $to ? 'end' : 'to')}->toDateTimeString()
                ];

                $start->{'startOf'.$range}()->{'add'.$range}();
            }
        }

        return collect($period);
    }


    /**
    * hasIndex
    *
    * Returns the indexed columns of a table or a certain indexName if provided
    * !IMPORTANT! The DB user must have access to information_schema for this to work!
    *
    * @param $db_name  | Database name
    * @param $table | Name of cable
    * @param $indexName | Name of the index
    * @return mixed
    */
    public function hasIndex($db_name = null, $table, $indexName = null)
    {
        if(!$db_name) {
            $db_name = env('DB_DATABASE');
        }

        $result =  \DB::table('information_schema.statistics')
            ->select('INDEX_NAME', 'COLUMN_NAME')
            ->where('TABLE_SCHEMA', '=', $db_name)
            ->where('TABLE_NAME', '=',$table);

        if($indexName) {
            $result = $result->where('INDEX_NAME', '=', $indexName);
        }

        $result = $result->get();

        if(count($result) || !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
    * createIndex
    *
    * Verifies if the index exists on the given table and creates it if it doesn't
    * Dependency on hasIndex() !
    *
    * @param $table | Name of table to alter
    * @param string $indexName | Name of the index
    * @param array $columns | array of columns to be indexed
    * @param string $indexType | Type of the index, default INDEX
    * @param null $db_name | Name of the database OPTIONAL
    */
    public function createIndex($table, $indexName, array $columns, $indexType = 'INDEX', $db_name = null)
    {
        if(! hasIndex($db_name, $table, $indexName)) {
            if($db_name) {
                $table = $db_name.'.'.$table;
            }

            var_dump('ALTER TABLE '.$table.' ADD '.$indexType.' '.$indexName.'('.implode(',', $columns).')');
            return \DB::statement('ALTER TABLE '.$table.' ADD '.$indexType.' '.$indexName.'('.implode(',', $columns).')');
        }

        return false;
    }


    /**
    * Encodes a image into base64 string
    * @param string $absolute_path
    * @return string
    */
    public function encodeImage64($absolute_path)
    {
        if(! \File::exists($absolute_path)) return false;

        $imgData = base64_encode(file_get_contents($absolute_path));
        $src = 'data: '.mime_content_type($absolute_path).';base64,'.$imgData;

        return $src;
    }


    /*
    * Returns the difference in days of 2 give dates
    */
    public function diffInDays($startDate, $endDate)
    {
        $startDate = new \DateTime(date('Y-m-d', strtotime($startDate)));
        $endDate = new \DateTime(date('Y-m-d', strtotime($endDate)));
        $period = $startDate->diff($endDate);

        return $period->days;
    }


    public function cleanChars($string)
    {
    // Put the special chars and coresponding html entities
        $specialCharacters = array(
            'ã' => '&#x103;',
            'ă' => '&#x103;',
            'Ă' => '&#x102;',
            'â' => '&#226;',
            'Â' => '&#194;',
            'î' => '&#238;',
            'Î' => '&#206;',
            'ș' => '&#x219;',
            'Ș' => '&#x219;',
            'ş' => '&#x219;',
            'Ş' => '&#x219;',
            'ț' => '&#x163;',
            'Ț' => '&#x162;',
            'ţ' => '&#x163;',
            'Ţ' => '&#x162;',
        );
        while (list($character, $replacement) = each($specialCharacters))
        {
            $string = str_replace($character, $replacement, $string);
        }
        return $string;
    }

    /**
    * Convert number to their word representation in romanian.
    * @param $No
    * @param string $sp
    * @param string $pct
    * @return string
    */
    public function convertNoToRon($No, $sp = ',', $pct = '.')
    {

        // numerele literal
        $na = ["", "Unu", "Doi", "Trei", "Patru", "Cinci", "Sase", "Sapte", "Opt", "Noua"];
        $nb = ["", "Un", "Doua", "Trei", "Patru", "Cinci", "Sase", "Sapte", "Opt", "Noua"];
        $nc = ["", "Una", "Doua", "Trei", "Patru", "Cinci", "Sase", "Sapte", "Opt", "Noua"];
        $nd = ["", "Unu", "Doua", "Trei", "Patru", "Cinci", "Sase", "Sapte", "Opt", "Noua"];

        // unitati
        $ua = ["", "Zece", "Zeci", "Zeci", "Zeci", "Zeci", "Zeci", "Zeci", "Zeci", "Zeci"];
        $ub = ["", "Suta", "Sute", "Sute", "Sute", "Sute", "Sute", "Sute", "Sute", "Sute"];
        $uc = ["", "Mie", "Mii"];
        $ud = ["", "Milion", "Milioane"];
        $ue = ["", "Miliard", "Miliarde"];

        // legatura intre grupuri
        $lg1 = ["", "Spre", "Spre", "Spre", "Spre", "Spre", "Spre", "Spre", "Spre", "Spre"];
        $lg2 = ["", "", "Si", "Si", "Si", "Si", "Si", "Si", "Si", "Si"];

        // moneda
        $mon = ["", " leu", " lei"];
        $ban = ["", " ban ", " bani "];

        //se elimina $sp din numar
        $sNo = (string)$No;
        $sNo = str_replace($sp, "", $sNo);

        //extrag partea intreaga si o completez cu zerouri la stg.
        $NrI = sprintf("%012s", (string)strtok($sNo, $pct));

        // extrag zecimalele
        $Zec = (string)strtok($pct);
        $Zec = substr($Zec . '00', 0, 2);

        // grupul 4 (miliarde)
        $Gr = substr($NrI, 0, 3);
        $n1 = (integer)$NrI[0];
        $n2 = (integer)$NrI[1];
        $n3 = (integer)$NrI[2];
        $Rez = $nc[$n1] . $ub[$n1];
        $Rez = ($n2 == 1) ? $Rez . $nb[$n3] . $lg1[$n3] . $ua[$n2] :
            $Rez . $nc[$n2] . $ua[$n2] . $lg2[$n2] . ($Gr == "001" || $Gr == "002" ? $nb[$n3] : $nd[$n3]);
        $Rez = ($Gr == "000") ? $Rez : (($Gr == "001") ? ($Rez . $ue[1]) : ($Rez . $ue[2]));

        // grupul 3 (milioane)
        $Gr = substr($NrI, 3, 3);
        $n1 = (integer)$NrI[3];
        $n2 = (integer)$NrI[4];
        $n3 = (integer)$NrI[5];

        $Rez = $Rez . $nc[$n1] . $ub[$n1];

        $Rez = ($n2 == 1) ? $Rez . $nb[$n3] . $lg1[$n3] . $ua[$n2] :
            $Rez . $nc[$n2] . $ua[$n2] . $lg2[$n2] . ($Gr == "001" || $Gr == "002" ? $nb[$n3] : $nd[$n3]);
        $Rez = ($Gr == "000") ? $Rez : (($Gr == "001") ? ($Rez . $ud[1]) : ($Rez . $ud[2]));

        // grupul 2 (mii)
        $Gr = substr($NrI, 6, 3);
        $n1 = (integer)$NrI[6];
        $n2 = (integer)$NrI[7];
        $n3 = (integer)$NrI[8];
        $Rez = $Rez . $nc[$n1] . $ub[$n1];
        $Rez = ($n2 == 1) ? $Rez . $nb[$n3] . $lg1[$n3] . $ua[$n2] :
            $Rez . $nc[$n2] . $ua[$n2] . $lg2[$n2] . ($Gr == "001" || $Gr == "002" ? $nc[$n3] : $nd[$n3]);
        $Rez = ($Gr == "000") ? $Rez : (($Gr == "001") ? ($Rez . $uc[1]) : ($Rez . $uc[2]));

        // grupul 1 (unitati)
        $Gr = substr($NrI, 9, 3);
        $n1 = (integer)$NrI[9];
        $n2 = (integer)$NrI[10];
        $n3 = (integer)$NrI[11];
        $Rez = $Rez . $nc[$n1] . $ub[$n1];
        $Rez = ($n2 == 1) ? ($Rez . $nb[$n3] . $lg1[$n3] . $ua[$n2] . $mon[2]) : ($Rez . $nc[$n2] . $ua[$n2] .
            ($n3 > 0 ? $lg2[$n2] : '') . ($NrI == "000000000001" ? ($nb[$n3] . $mon[1]) : ($na[$n3]) . $mon[2]));

        if ((integer)$NrI == 0)
        {
            $Rez = "";
        }

        // banii
        if ((integer)$Zec > 0)
        {
            $n2 = (integer)substr($Zec, 0, 1);
            $n3 = (integer)substr($Zec, 1, 1);
            $Rez .= ' si ';
            $Rez = ($n2 == 1) ? ($Rez . $nb[$n3] . $lg1[$n3] . $ua[$n2]) :
                ($Rez . $nc[$n2] . $ua[$n2] . $lg2[$n2] . ($Zec == "01" ? ($nb[$n3] . $ban[1]) : ($na[$n3]) . $ban[2]));
        }
        $Rez = strtolower($Rez);

        return $Rez;
    }

    /**
    * Creates a single query for multiple row updates.
    *
    * @param  (tableName) Table name to be updated
    * @param  (data) Format ['col1' => [ $id => $val, $id2 => $val2 ], ['col2'=> [$id => $val, $id2 => $val2] ]
    * @param  (caseColumn) Name of the column in CASE
    * @param  (whereIds) (optional) If you have a array of ids and the caseColumn data type is numeric.
    * The query string must be used in a DB::update(DB::raw($query_string))
    */
    public function bulkUpdateMultiple($tableName, $data, $caseColumn, $whereIds = false)
    {
        $sql = 'UPDATE `'.$tableName.'` SET ';
        $whereIn = [];

        foreach($data as $column => $rows) {
            $sql .= ' `'.$column.'` = ';
            $sql .= 'CASE `'.$caseColumn.'`';

            foreach($rows as $caseValue => $val) {
                $caseValue = (is_numeric($caseValue)) ? $caseValue : '"' . $caseValue . '"';
                $val = (is_numeric($val)) ?  $val : '"' . $val . '"';


                //custom addition
                if($column == 'tag') {
                    $val = removeQuotes($val);
                }

                $sql .= ' WHEN ' . $caseValue.' ';
                $sql .= ' THEN ' . $val.' ';

                $whereIn[$caseValue] = $caseValue;
            }
            $sql .= 'ELSE "" END,';
        }

        $sql = rtrim($sql , ',');
        $sql .= ' WHERE `' .$caseColumn. '` IN(';

        if($whereIds) {
            $whereIn = $whereIds;
        }

        $inString = '';

        foreach($whereIn as $value) {
            $inString .= (is_numeric($value)) ?  $value . ',' : '"' . $value . '",';
        }

        $inString = rtrim($inString, ',');
        $sql .= $inString;
        $sql .= ')';

        return $sql;
    }


    public function subval_sort($a, $subkey, $sort = 'DESC') {
        foreach($a as $k=>$v) {
            $b[$k] = strtolower($v[$subkey]);
        }

        if($sort == 'ASC') {
            asort($b);
        } else {
            arsort($b);
        }
        foreach($b as $key=>$val) {
            $c[] = $a[$key];
        }

        return $c;
    }
}