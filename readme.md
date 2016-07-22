# Welcome

This is a fork of PHP MySQL class, which utilizes MySQLi.
Fixes some bugs and allows for improvements of original class.

It is inspired from the CodeIgniter php framework.
Most of the functions and documentation are identical to CodeIgniter database class and the user guide is derived from it. 

You do not need to use CodeIgniter to use this class. Simply include the class file and you are good to go.

PHP 5.3 + mysqli are required. 


# Documentation

## Usage

To use this class, import it to your project


```
require_once 'class.database.php' ;

```

Once the class file is included, initialize it

```
$db = new Database($host, $username, $password, $database);

```

If your MySQL installation is using a non standard port, you can specify the port as 

```
$db = new Database($host, $username, $password, $database, $port);

```

If you are going to use a table prefix, you can assign it as 

```
$db->set_table_prefix('wp_'); // Sets wp_ as table prefix

```


## SELECT Query

A query string can be generated in two ways

1. Manual query 
2. Using active records

## Manual : $db->query()

```
$sql = "SELECT * FROM table" ;
$db->query($sql) ;

```


## Get only the first row : $db->query_first() 

query_first() can be used to get the first row of the query. 

```
$db->query_first("SELECT * FROM table") ;
// Produces: SELECT * FROM table LIMIT 1 ;
```

## Executing Query : $db->execute()

```
$sql = "SELECT * FROM table" ;
$db->query($sql) ;
$db->execute(); 

// Also using method chaining

$sql = "SELECT * FROM table";
$db->query($sql)->execute() ;

echo "Affected Rows : " . $db->affected_rows ;  // Outputs the affected rows

```

## Using Active Record Pattern

## SELECT Query : $db->select()->from()

The following function permits you to write the SELECT portion of your query

```
$db->select('title, content, date');
// Produces: SELECT title, content, date

$db->select('*');
// Produces: SELECT *

```

If you do not call the select() method, "SELECT *" will be assumed. If no parameter is given, select() will assume *


## DISTINCT : $db->distinct();

```
$db->distinct();
// Produces: SELECT DISTINCT
```


## FROM clause : $db->from()

```
$db->from('table') ; // Set the table name

```

You can also chain the methods such as 

```
$db->select("id, email")->from('table') ;
// Produces : SELECT id, email FROM table

```

## WHERE clause : $db->where()

The general syntax for where() is 

```
$db->where('a', 'b' ); 
$db->where('c', 'd' ); 

```
You can also feed an array as

```
$db->where( array
          ('a' => 'b',
           'c' => 'd'
) ;
```


```
$where = array(
     'name' => 'test',
     'email' => 'email@example.com'
);

$db->where($where);
// Produces: WHERE name = 'test' AND email = 'email' 
// Using method chaining

$db->select()->from('table')->where($where) ;
// Produces: SELECT * FROM table WHERE name = 'test' AND email = 'email' 

// You can also skip select() if you want. 
$db->from('table')->where($where) ;
// Produces: SELECT * FROM table WHERE name = 'test' AND email = 'email' 

```

## OR_WHERE clause : $db->or_where()

```
$db->where('name !=', $name);
$db->or_where('id >', $id);
// Produces: WHERE name != 'Joe' OR id > 50


```

## WHERE IN: $db->where_in()

Generates a WHERE field IN ('item', 'item') SQL query joined with AND if appropriate

```
$names = array('Frank', 'Todd', 'James');
$db->where_in('username', $names);
// Produces: WHERE username IN ('Frank', 'Todd', 'James')

```

## OR WHERE IN: $db->or_where_in()

Generates a WHERE field IN ('item', 'item') SQL query joined with OR if appropriate

```
$names = array('Frank', 'Todd', 'James');
$db->or_where_in('username', $names);
// Produces: OR username IN ('Frank', 'Todd', 'James')
```

## WHERE NOT IN : $db->where_not_in()

Generates a WHERE field NOT IN ('item', 'item') SQL query joined with AND if appropriate

```
$names = array('Frank', 'Todd', 'James');
$db->where_not_in('username', $names);
// Produces: WHERE username NOT IN ('Frank', 'Todd', 'James')
```

## OR WHERE NOT IN: $db->or_where_not_in()

Generates a WHERE field NOT IN ('item', 'item') SQL query joined with OR if appropriate

```
$names = array('Frank', 'Todd', 'James');
$db->or_where_not_in('username', $names);
// Produces: OR username NOT IN ('Frank', 'Todd', 'James')
```


## Parenthesis between WHERE 

To open a parenthesis, use open_where() and to close use close_where()
```
$db->select('column')->from('table');
$db->where('foo', 15);
$db->open_where();
$db->or_where('foo <', 15);
$db->where('bar >=', 15);
$db->close_where();
// Produces  SELECT `column` FROM `table` WHERE `foo` = 15 OR (`foo` < 15 AND `bar` >= 15) 
```


## SELECT + FROM + WHERE + Execute

The following example combines all the function to get the result easily

```
$db->select()->from('table')->where($where)->execute(); 
$affected = $db->affected_rows ; // Gets the total number of rows selected 

// Again, you can skip the select() method if you are selecting all fields (*)
$db->from('table')->where($where)->execute();
```

## Fetching the result : $db->fetch()

The result will be output as associative array when the fetch() is called. You do not need to call execute() before you call fetch(). The functions execute() and fetch() acts like same. The former does not return data and the latter returns an array with the data. In both the cases, $db->affected_rows will be set. 

```
$rows = $db->from('table')->where($where)->fetch(); 
echo $db->affected_rows ; // Output the total number of selected rows 

foreach($rows as $row )
{
   var_dump($row);
}

// Or in short

$rows = $db->from('table')->fetch();
var_dump($rows);
// Produces: SELECT * FROM table
```

## Fetching the first row : $db->fetch_first() or $db->fetch_result()

This function will return only the first row of the result

```
$array = $db->from('table')->fetch_first() ;

// Produces SELECT * FROM table LIMIT 1
// Returns an array
```


## LIMIT and OFFSET : $db->limit()

```
$db->limit(1); 

// Produces the limit part :  LIMIT 1

$db->limit(1,2);

// Produces the limit and offset : LIMIT 1,2 

// Example

$db->select()->from('table')->limit(1,5)->execute();
// Produces: SELECT * FROM table LIMIT 1, 5
```

## GET : $db->get() ( from version 1.5.1)

Get  function saves time by calling multiple functions at once.


```
$db->select('*')->from('table')->fetch();
```

The same code can be expressed using 

```
$db->get('table');
```

Get also takes Limit as the second parameter and Offset as the third parameter. These parameters are optional.

``
$db->select('*')->from('table')->limit(1,2)->fetch();
``

is equivalent to 

```
$db->get('table', 1, 2);
```

 
## SELECT_MAX : $db->select_max()

Writes a "SELECT MAX(field)" portion for your query. You can optionally include a second parameter to rename the resulting field.

```
$result = $db->select_max('age')->from('table')->fetch();
// Produces: SELECT MAX(age) AS age FROM members

$result = $db->select_max('age', 'member_age')->from('table')->fetch();;
// Produces: SELECT MAX(age) AS member_age FROM members
```

## SELECT_MIN : $db->select_min()

Writes a "SELECT MIN(field)" portion for your query. As with select_max(), You can optionally include a second parameter to rename the resulting field.

```
$result = $db->select_min('age')->from('table')->fetch();
// Produces: SELECT MIN(age) AS age FROM members

$result = $db->select_min('age', 'member_age')->from('table')->fetch();;
// Produces: SELECT MIN(age) AS member_age FROM members
```

## SELECT_AVG : $db->select_avg()

Writes a "SELECT AVG(field)" portion for your query. As with select_max(), You can optionally include a second parameter to rename the resulting field.

```
$result = $db->select_avg('age')->from('table')->fetch();
// Produces: SELECT AVG(age) AS age FROM members

$result = $db->select_avg('age', 'member_age')->from('table')->fetch();;
// Produces: SELECT AVG(age) AS member_age FROM members
```



## SELECT_SUM : $db->select_sum()

Writes a "SELECT SUM(field)" portion for your query. As with select_max(), You can optionally include a second parameter to rename the resulting field.

```
$result = $db->select_sum('age')->from('table')->fetch();
// Produces: SELECT SUM(age) AS age FROM members

$result = $db->select_sum('age', 'member_age')->from('table')->fetch();;
// Produces: SELECT SUM(age) AS member_age FROM members
```



## Inserting Data :  $db->insert() or $db->insert_ignore()

```
$data = array(
    'title' => 'Some title',
    'email' => 'email@example.com', 
    'created' => 'NOW()'

);

$id = $db->insert('tableName', $data) ; // $id will have the auto-increment 

echo "Data inserted. ID:" . $id ;
```



## Update query : $db->update()

update() method can be used to update a table with the data. Update() expects that you already set the WHERE clause and LIMIT before calling update().


```
$where = array(
         'email' = > 'test@test.com',
         'id' => 14
);

$data = array(
         'email' => 'new@example.com',
         'password' => 'pass1'
);

$db->where($where)->update('table', $data); 
// Produces: UPDATE table SET email = 'new@example.com', password = 'pass1' WHERE email = 'test@test.com' AND id = '14' ;

// NOTE: where() should be called BEFORE update(), otherwise it will be ignored. 

// You can also use or_where()

$db->or_where($where)->update('table', $data); 
// Produces: UPDATE table SET email = 'new@example.com', password = 'pass1' WHERE email = 'test@test.com' OR id = '14' ;
```

## Last Query : $db->last_query()

This function will return the last generated query string. Useful for debugging purpose.

```
$this->select('id')->from('table')->where('name', 'test')->execute();
echo $db->last_query();

// Produces: SELECT id FROM table where name = 'test' ;

```


## Dry Run : $db->dryrun() 

Dry run will output the query string which is ready to be executed. If you call dryrun() then the query will not be executed. And the last_query() will output the query which is ready to be executed.

This function is often useful in case of calling DELETE or UPDATE function. The developer can view the DELETE or UPDATE query generated, before executing it.

```
$data['email'] = 'db@example.com';

echo $db->dryrun()->update('table', $data)->last_query();

// Produces: UPDATE table SET email = 'db@example.com' , and the query is NOT executed. 
```


## Escape String : $db->escape()

This function returns sanitized data for mysql operation

```
$string = "where 's a and 's" ;
echo $db->escape($string);

// Produces: where \'s a and \'s
```

## LIKE : $db->like()

This function enables you to generate LIKE clauses, useful for doing searches.

Simple key/value method:
```
$db->like('title', 'match');
// Produces: WHERE title LIKE '%match%' 
```

If you use multiple function calls they will be chained together with AND between them:

```
$db->like('title', 'match');
$db->like('body', 'match');

// WHERE title LIKE '%match%' AND body LIKE '%match%
```

If you want to control where the wildcard (%) is placed, you can use an optional third argument. Your options are 'before', 'after' and 'both' (which is the default). 

```
$db->like('title', 'match', 'before');
// Produces: WHERE title LIKE '%match'

$db->like('title', 'match', 'after');
// Produces: WHERE title LIKE 'match%'

$db->like('title', 'match', 'both');
// Produces: WHERE title LIKE '%match%' 
```

If you do not want to use the wildcard (%) you can pass to the optional third argument the option 'none'. 

```
$db->like('title', 'match', 'none');
// Produces: WHERE title LIKE 'match' 
```

Associative array method

```
$array = array('title' => $match, 'page1' => $match, 'page2' => $match);

$db->like($array);

// WHERE title LIKE '%match%' AND page1 LIKE '%match%' AND page2 LIKE '%match%'
```

## OR LIKE : $db->or_like()

This function is identical to the one above, except that multiple instances are joined by OR:

```
$db->like('title', 'match');
$db->or_like('body', $match);

// WHERE title LIKE '%match%' OR body LIKE '%match%'
```


## HAVING : $db->having()

Permits you to write the HAVING portion of your query. There are 2 possible syntaxes, 1 argument or 2:

```
$db->having('user_id = 45');
// Produces: HAVING user_id = 45

$db->having('user_id', 45);
// Produces: HAVING user_id = 45
```

You can also pass an array of multiple values as well:

```
$db->having(array('title =' => 'My Title', 'id <' => $id));
// Produces: HAVING title = 'My Title', id < 45
```

## OR HAVING : $db->or_having()

Identical to having(), only separates multiple clauses with "OR".

## GROUP BY : $db->group_by()

Permits you to write the GROUP BY portion of your query:

```
$db->group_by("title");
// Produces: GROUP BY title 
```

You can also pass an array of multiple values as well:

```
$db->group_by(array("title", "date"));
// Produces: GROUP BY title, date
```

## ORDER BY : $db->order_by()

Lets you set an ORDER BY clause. The first parameter contains the name of the column you would like to order by. The second parameter lets you set the direction of the result. Options are asc or desc ( case insensitive) 

```
$db->order_by("title", "desc");
// Produces: ORDER BY title DESC 
```
You can also pass your own string in the first parameter:

```
$db->order_by('title desc, name asc');
// Produces: ORDER BY title desc, name asc
```

Or multiple function calls can be made if you need multiple fields.

```
$db->order_by("title", "desc");
$db->order_by("name", "asc");
// Produces: ORDER BY title DESC, name ASC 
```

In addition to this, you can also use an array for multiple calls

```
$order_by = array('title' => 'desc',
                  'name'  => 'asc'
);

$db->order_by($order_by);
// Produces: ORDER BY title DESC, name ASC 
```

## DELETE : $db->delete();

Permits you to write DELETE statement. Delete function takes one optional argument - table. Also note that delete() requires that you call execute() at the end to execute the query

```
$db->delete('table')->execute();
// Produces: DELETE FROM table
```

If table is not provided, it will take the table name set by '$db->from()' method

```
$db->delete()->from('table')->execute();
// Produces: DELETE FROM table
```

You can also use where(), limit(), having(), like() etc with delete method

```
$db->delete()->from('table')->where('email', 'test@example.com')->limit(1)->execute();
// Produces: DELETE FROM table WHERE email = 'test@example.com' LIMIT 1
```

delete() will also gives you the total number of rows deleted.

```
$db->delete()->from('table')->execute();
echo "Deleted Rows: " . $db->affected_rows ;
```

## Dry Run on DELETE query

Delete is a dangerous query which will irreversibly delete your data from the table. If you are not sure how the delete query will be generated, you can run dryrun() and it will give you the generated query which is ready to be executed.

```
$db->dryrun()->delete('table')->exexute()->last_query();
// Outputs DELETE FROM table , but does not execute the query 
```


## JOIN : $db->join();

Permits you to write JOIN statement. 


```
$db->select('*');
$db->from('blogs');
$db->join('comments', 'comments.id = blogs.id');

// Produces:
// SELECT * FROM blogs
// JOIN comments ON comments.id = blogs.id
```

Multiple function calls can be made if you need several joins in one query. If you need a specific type of JOIN you can specify it via the third parameter of the function. Options are: left, right, outer, inner, left outer, and right outer.


```
$db->join('comments', 'comments.id = blogs.id', 'left');
// Produces: LEFT JOIN comments ON comments.id = blogs.id
```


## FIND_IN_SET : $db->find_in_set()   ( from version 1.4.3)

Generates a FIND_IN_SET query. Takes 3 parameters. First parameter is the string to find. Second parameter is the column name to search, and third parameter which is optional, will take an operator AND or OR to join multiple WHERE clauses. 

```
$db->find_in_set('503', 'orders')->from('tblinvoices')->fetch();
// Produces: SELECT *  FROM tblinvoices  WHERE  FIND_IN_SET ('305', orders) 

$db->where('id', 5)->find_in_set('503', 'orders')->from('tblinvoices')->fetch();
// Produces: SELECT *  FROM tblinvoices WHERE id='5' AND  FIND_IN_SET ('305', orders) 

$db->where('id', 5)->find_in_set('503', 'orders', 'OR')->from('tblinvoices')->fetch();
// Produces: SELECT *  FROM tblinvoices WHERE id='5' OR  FIND_IN_SET ('305', orders) 
```

## BETWEEN : $db->between()   ( from version 1.4.6)

Generates a BETWEEN condition. 

```
$db->between('created', '2014-05-05', '2014-05-10');
// Produces: created BETWEEN '2014-05-05' AND '2014-05-10'

$db->from('tblinvoices')->where('clientid', '12')->between('created', '2014-05-05' , '2014-05-10')->fetch();
// Produces: SELECT * FROM tblinvoices WHERE clientid = '12' AND created BETWEEN '2014-05-05' AND '2014-05-10'
```
