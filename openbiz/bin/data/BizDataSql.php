<?PHP
/**
 * BizDataSql class - class BizObjSql is the class to constrcut SQL statement for BizDataObj
 *
 * @package BizDataObj
 * @author rocky swen
 * @copyright Copyright (c) 2005
 * @access private
 */
class BizDataSql {
   protected $m_TableColumns = null;
   protected $m_TableJoins = null;
   protected $m_JoinAliasList = array();
   protected $m_TableAliasList = array();
   protected $m_SqlWhere = null;
   protected $m_OrderBy = null;
   protected $m_OtherSQL = null;
   protected $m_AliasIndex = 0;
   protected $m_MainTable;

   public function __construct()
   {
   }

   /**
    * BizDataSql::AddMainTable() - add main table in the sql statement T0 alias
    *
    * @param string $mainTable main table name
    * @return void
    **/
   public function AddMainTable($mainTable)
   {
      $this->m_MainTable = $mainTable;
      $this->m_TableJoins = " $mainTable T0 ";
   }

   /**
    * BizDataSql::AddJoinTable() - add a join table in the sql statement Ti alias
    *
    * @param TableJoin $tableJoin table join object
    * @return void
    **/
   /* SELECT T1.col, T2.col
      FROM table1 T1
           INNER JOIN table2 T2 ON T1.col1=T2.col1
           LEFT JOIN  table3 T3 ON T1.col1=T3.col1
      WHERE
   */
   public function AddJoinTable($tableJoin)
   {
      $table = $tableJoin->m_Table;
      $joinType = $tableJoin->m_JoinType;
      $column = $tableJoin->m_Column;
      $joinRef = $tableJoin->m_JoinRef;
      $columnRef = $tableJoin->m_ColumnRef;

      $alias = "T".(count($this->m_JoinAliasList)+1);   // start with T1, T2
      $this->m_JoinAliasList[$tableJoin->m_Name] = $alias;
      $this->m_TableAliasList[$table] = $alias;
      $aliasRef = $this->GetJoinAlias($joinRef);
      $this->m_TableJoins .= " $joinType $table $alias ON $alias.$column = $aliasRef.$columnRef ";
   }

   /**
    * BizDataSql::AddTableColumn() - add a join table and cloumn in the sql statement
    *
    * @param string $join table join name
    * @param string $column column name
    * @return void
    **/
   public function AddTableColumn($join, $column)
   {
      $tcol = $this->GetTableColumn($join, $column);
      if (!$this->m_TableColumns)
         $this->m_TableColumns = $tcol;
      else
         $this->m_TableColumns .= ", ".$tcol;
   }

   /**
    * BizDataSql::AddSqlExpression() - add SQL expression in the sql statement
    * sqlExpr has format of "...join1.column1, ... join2.column2...". Replace join with alias
    *
    * @param string $sqlExpr sql expression
    * @return void
    **/
   public function AddSqlExpression($sqlExpr)
   {
      if (!$this->m_TableColumns)
         $this->m_TableColumns = $sqlExpr;
      else
         $this->m_TableColumns .= ", ".$sqlExpr;
   }

   public function GetJoinAlias($join)
   {
      if (!$join) // main table, no join
         return "T0";
      else
         return $this->m_JoinAliasList[$join];
   }

   /**
    * BizDataSql::GetTableColumn()
    * Combine a table with a column.
    *
    * @param string $join join name
    * @param string $col column
    * @return string table column combination string
    */
   public function GetTableColumn($join, $col)
   {
      // check the function format on $col
      $alias = $this->GetJoinAlias($join);
      return "$alias.$col";
   }

   public function ResetSQL()
   {
      $this->m_SqlWhere = null;
      $this->m_OrderBy = null;
      $this->m_OtherSQL = null;
   }

   /**
    * BizDataSql::AddSqlWhere()
    * Add the where clause (search rule) into the SQL statement
    *
    * @param string $sqlwhere SQL WHERE clause
    * @return void
    */
   public function AddSqlWhere($sqlwhere)
   {
      if ($sqlwhere == null)
         return;
      if ($this->m_SqlWhere == null) {
         $this->m_SqlWhere = $sqlwhere;
      } elseif (strpos($this->m_SqlWhere, $sqlwhere) === false) {
         $this->m_SqlWhere .= " AND " . $sqlwhere;
      }
   }

   /**
    * BizDataSql::AddOrderBy()
    *
    * @param string $orderby SQL ORDER BY clause
    * @return void
    **/
   public function AddOrderBy($orderby)
   {
      if ($orderby == null)
         return;
      if ($this->m_OrderBy == null) {
         $this->m_OrderBy = $orderby;
      } elseif (strpos($this->m_OrderBy, $orderby) === false) {
         $this->m_OrderBy .= " AND " . $orderby;
      }
   }

   /**
    * BizDataSql::AddOtherSQL()
    *
    * @param string $otherSQL additional SQL statment
    * @return void
    **/
   public function AddOtherSQL($otherSQL)
   {
      if ($otherSQL == null)
         return;
      if ($this->m_OtherSQL == null) {
         $this->m_OtherSQL = $otherSQL;
      } elseif (strpos($this->m_OtherSQL, $otherSQL) === false) {
         $this->m_OtherSQL .= " AND " . $otherSQL;
      }
   }

   /**
    * BizDataSql::AddAssociation()
    *
    * @param array $assc additional SQL statment
    * @return void
    **/
   public function AddAssociation($assc)
   {
      $where = "";
      if ($assc["Relationship"] == "1-M" || $assc["Relationship"] == "M-1" || $assc["Relationship"] == "1-1") {
         // assc table should same as maintable
         if ($assc["Table"] != $this->m_MainTable) return;
         // add table to join table
         $mytable_col = $this->GetTableColumn(null, $assc["Column"]);
         // construct table.column = 'field value'
         $where = $mytable_col." = '".$assc["FieldRefVal"]."'";
      }
      else if ($assc["Relationship"] == "M-M") {
         // ... INNER JOIN xtable TX ON TX.column2 = T0.column
         // WHERE ... Tx.column1 = 'PrtTableColumnValue'
         $mytable_col = $this->GetTableColumn(null, $assc["Column"]);   // this table's alias.column
         $xtable = $assc["XTable"];    // xtable's name
         $column1 = $assc["XColumn1"]; // xtable column1
         $column2 = $assc["XColumn2"]; // xtable column2
         $xalias = "TX";
         if (isset($this->m_TableAliasList[$xtable]))
            $xalias = $this->m_TableAliasList[$xtable];
         // add a table join for the xtable if such join of the table is not there before (note: may report error if has same join).
         //if (strpos($this->m_TableJoins, "JOIN $xtable") === false)
         if (!isset($this->m_TableAliasList[$xtable])) {
            $this->m_TableJoins .= " INNER JOIN $xtable $xalias ON $xalias.$column2 = $mytable_col ";
            $this->m_TableAliasList[$xtable] = $xalias;
         }
         // add a new where condition
         $where = "$xalias.$column1 = '".$assc["FieldRefVal"]."'";
      }

      if (strlen($where) > 1)
         $this->AddSqlWhere($where);
   }

   /**
    * BizDataSql::GetSqlStatement()
    * Get the SQL statement
    *
    * @return SQL statement string
    */
   public function GetSqlStatement()
   {
      $ret = "SELECT " . $this->m_TableColumns;
      $ret .= " FROM " . $this->m_TableJoins;
		if ($this->m_SqlWhere != null) {
		   $ret .= " WHERE " . $this->m_SqlWhere;
		}
		/*if ($this->m_OrderBy != null) {
		   $ret .= " ORDER BY " . $this->m_OrderBy;
		}*/
		if ($this->m_OtherSQL != null) {
		   $ret .= " " . $this->m_OtherSQL;
		}
		if ($this->m_OrderBy != null) {
		   $ret .= " ORDER BY " . $this->m_OrderBy;
		}
      return $ret;
   }
}
?>
