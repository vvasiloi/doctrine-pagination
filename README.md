## Setup

- `docker run --name symfony-mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=symfony -e MYSQL_USER=symfony -e MYSQL_PASSWORD=symfony -p "33060:3306" -d mysql:5.7`
- set the proper database host in `.env` (it differs depending on the host and Docker setup)
- `composer install`
- `php bin/console doctrine:schema:create`
- `php bin/console doctrine:fixtures:load`

## Usage
### Current behavior

`php bin/console product:list category-1`

  id | code       | fetched-categories (position) | actual-categories (position)                     
 ----|------------|-------------------------------|--------------------------------------------------
  3  | product-3  | category-1 (0)                | category-1 (0), category-2 (2), category-3 (1)
  4  | product-4  | category-1 (1)                | category-1 (1), category-3 (2), category-5 (2)
  5  | product-5  | category-1 (2)                | category-1 (2), category-2 (3), category-5 (3)

Only the matched category is fetched.

### With double join on the product categories

`php bin/console product:list --with-double-join category-1`

  id | code       | fetched-categories (position)                    | actual-categories (position)                     
 ----|------------|--------------------------------------------------|--------------------------------------------------
  3  | product-3  | category-1 (0), category-2 (2), category-3 (1)   | category-1 (0), category-2 (2), category-3 (1)
  4  | product-4  | category-1 (1), category-3 (2), category-5 (2)   | category-1 (1), category-3 (2), category-5 (2)
  5  | product-5  | category-1 (2), category-2 (3), category-5 (3)   | category-1 (2), category-2 (3), category-5 (3)

All product categories are fetched. Every things seems good...

### But it actually breaks when limit kicks in

`php bin/console product:list --with-double-join --limit=1 category-1`

  id | code      | fetched-categories (position) | actual-categories (position)                   
 ----|-----------|-------------------------------|------------------------------------------------
  3  | product-3 | category-1 (0)                | category-1 (0), category-2 (2), category-3 (1)

Not only some product categories are missing, but also some products.

### This can be fixed by passing `$fetchJoinCollection = true` to the `Paginator`

`php bin/console product:list --with-double-join --fetch-join-collection --limit=2 category-1`

  id | code      | fetched-categories (position)                  | actual-categories (position)                   
 ----|-----------|------------------------------------------------|------------------------------------------------
  3  | product-3 | category-1 (0), category-2 (2), category-3 (1) | category-1 (0), category-2 (2), category-3 (1)
  4  | product-4 | category-1 (1), category-3 (2), category-5 (2) | category-1 (1), category-3 (2), category-5 (2)

  
This seems to fix the issues, but it breaks again when there's an order by a field from a to-many association and output walkers are disabled.

`php bin/console product:list --with-double-join --fetch-join-collection --without-output-walkers --with-order-by --limit=3 category-1`

It throws the following exception

```
In LimitSubqueryWalker.php line 150:
                                                                                                                                                  
  Cannot select distinct identifiers from query with LIMIT and ORDER BY on a column from a fetch joined to-many association. Use output walkers.                                                                                                                                           

```

Therefore disabling output walkers by default seems like a bad decision.

`php bin/console product:list --with-double-join --fetch-join-collection --with-output-walkers --with-order-by --limit=3 category-1`

  id | code      | fetched-categories (position)                  | actual-categories (position)                   
 ----|-----------|------------------------------------------------|------------------------------------------------
  3  | product-3 | category-3 (1), category-1 (0), category-2 (2) | category-1 (0), category-2 (2), category-3 (1)
  4  | product-4 | category-1 (1), category-3 (2), category-5 (2) | category-1 (1), category-3 (2), category-5 (2)
  5  | product-5 | category-1 (2), category-2 (3), category-5 (3) | category-1 (2), category-2 (3), category-5 (3)
