<?php

namespace Tests\Feature;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
  use RefreshDatabase;  
  protected function setUp(): void
  {
    parent::setUp();
    DB::statement("create table if not exists categories (
        id varchar(100) not null primary key,
        name varchar(100) not null,
        created_at timestamp
    )");
    DB::statement("create table if not exists product (
        id VARCHAR(100) NOT NULL PRIMARY KEY,
        name VARCHAR(225) NOT NULL ,
        description TEXT,
        price INT NOT NULL DEFAULT 0,
        category_id VARCHAR(100) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_category_id foreign key(category_id) REFERENCES categories(id))");
    DB::delete("delete from categories");
    DB::delete("delete from product");
  } 
  public function testInsert() {
    DB::table('categories')->insert([
      'id' => 'LAPTOP',
      'name' => 'Laptop',
    ]);
    DB::table('categories')->insert([
      'id' => 'FOOD',
      'name' => 'Food',
    ]);

    $result = DB::select('SELECT COUNT(id) as total FROM categories');

    self::assertEquals(2, $result[0]->total);
  }

  public function testSelect() {
    $this->insertCategories();

    $collection = DB::table('categories')->select('id', 'name')->get();
    $this->assertCount(5, $collection);
    
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function insertCategories() {
    DB::table('categories')
    ->insert(['id' => 'FOOD', 'name' => 'Food', 'created_at' => '2022-01-01 00:00:00']);
    DB::table('categories')
    ->insert(['id' => 'FASHION', 'name' => 'Fashion', 'created_at' => '2022-01-01 00:00:00']);
    DB::table('categories')
    ->insert(['id' => 'LAPTOP', 'name' => 'Laptop', 'created_at' => '2022-01-01 00:00:00']);
    DB::table('categories')
    ->insert(['id' => 'MEJA', 'name' => 'Meja', 'created_at' => '2022-01-01 00:00:00']);
    DB::table('categories')
    ->insert(['id' => 'GADGET', 'name' => 'Gadget', 'created_at' => '2022-01-01 00:00:00']);
  }

  public function testWhere() {
    $this->insertCategories();

    $collection = DB::table('categories')->where(function(Builder $builder) {
      $builder->where('id', '=', 'FASHION');
      $builder->orWhere('id', '=', 'LAPTOP');
    })->get();
    $this->assertCount(2, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function testWhereBetween() {
    $this->testWhere();

    $collection = DB::table('categories')->whereBetween('created_at', ['2022-01-01 00:00:00', '2022-02-01 00:00:00'])->get();
    $this->assertCount(5, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function testWhereIn() {
    $this->insertCategories();

    $collection = DB::table('categories')->whereIn('id', ['FOOD', 'FASHION'])->get();
    $this->assertCount(2, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function testWhereNull() {
    $this->testWhere();

    $collection = DB::table('categories')->whereNull('description')->get();
    $this->assertCount(0, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function testWhereDate() {
    $this->insertCategories();

    $collection = DB::table('categories')->whereDate('created_at', '2022-01-01')->get();
    $this->assertCount(5, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function testUpdate(){
    $this->insertCategories();
    DB::table('categories')->where('id', '=', 'FOOD')->update([
      'name' => 'Makanan'
    ]);

    $collection = DB::table('categories')->where('name', '=', 'Makanan')->get();
    $this->assertCount(1, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));  
    });
  }

  public function testUpsert() {
    $this->insertCategories();
    DB::table('categories')->updateOrInsert([
      'id' => 'FOOD'
    ], [
      'name' => 'Makanan'
    ]);

    $collection = DB::table('categories')->where('name', '=', 'Makanan')->get();
    $this->assertCount(1, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));  
    });
  }

  public function testDelete() {
    $this->insertCategories();

    DB::table('categories')->where('id', '=', 'LAPTOP')->delete();

    $collection = DB::table('categories')->where('id', '=', 'LAPTOP')->get();
    $this->assertCount(0, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));  
    });
  }

  public function insertTableProduct() {
    $this->insertCategories();
    DB::table('product')
    ->insert(['id' => '1', 'name' => 'Gadget','description' => 'Gadget', 'created_at' => '2022-01-01 00:00:00', 'category_id' => 'GADGET']);
    DB::table('product')
    ->insert(['id' => '2', 'name' => 'Food', 'description' => 'Food', 'created_at' => '2022-01-01 00:00:00', 'category_id' => 'FOOD']);
    DB::table('product')
    ->insert(['id' => '3', 'name' => 'Fashion', 'description' => 'Fashion', 'created_at' => '2022-01-01 00:00:00', 'category_id' => 'FASHION']);
    DB::table('product')
    ->insert(['id' => '4', 'name' => 'Meja', 'description' => 'Meja', 'created_at' => '2022-01-01 00:00:00', 'category_id' => 'MEJA']);
    DB::table('product')
    ->insert(['id' => '5', 'name' => 'Laptop', 'description' => 'Laptop', 'created_at' => '2022-01-01 00:00:00', 'category_id' => 'LAPTOP']);
  }

  public function testQueryBuilderJoin( ) {
    $this->insertTableProduct();

    $collection = DB::table('product')
    ->join('categories', 'product.category_id', '=', 'categories.id')
    ->select('product.id','product.name', 'categories.name as category_name')->get();
    $this->assertCount(5, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function testOrdering() {
    $this->insertTableProduct();

    $collection = DB::table('product')
    ->whereNotNull('id')->orderBy('name', 'desc')->get();
    $this->assertCount(5, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function testPagination() {
    $this->insertTableProduct();

    $collection = DB::table('product')->skip(2)->take(2)->get();
    $this->assertCount(2, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function insertManyCategories() {
    for ($i = 0; $i < 100; $i++) {
      DB::table('categories')->insert([
        'id' => 'CAT' . $i,
        'name' => 'Category ' . $i,
        'created_at' => '2022-01-01 00:00:00'
      ]);
    }
  }

  public function testChunk() {
    $this->insertManyCategories();
      Log::info('start chunk');

    DB::table('categories')->chunkById(10, function ($categories) {
      self::assertNotNull($categories);
      $categories->each(function ($item) {
        Log::info(json_encode($item));
      });
    });
      Log::info('end chunk');

  }

  public function testLazy() {
    $this->insertManyCategories();

    $collection = DB::table('categories')->orderBy('id')->lazy(10)->take(5);
    self::assertNotNull($collection);

    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function testCursor() {
    $this->insertManyCategories();

    $collection = DB::table('categories')->orderBy('id')->cursor();
    self::assertNotNull($collection);

    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function insertProductWithPrice() {
    $this->insertCategories();

    DB::table('product')->insert(['id' => '1','name' => 'Samsung','description' => 'Samsung','created_at' => '2022-01-01 00:00:00','category_id' => 'GADGET','price' => 8500]);
    DB::table('product')->insert(['id' => '2','name' => 'Asus','description' => 'Asus','created_at' => '2022-01-01 00:00:00','category_id' => 'LAPTOP','price' => 15000]);
    DB::table('product')->insert(['id' => '3','name' => 'Rollet','description' => 'Rollet','created_at' => '2022-01-01 00:00:00','category_id' => 'FASHION','price' => 160000]);
    DB::table('product')->insert(['id' => '4','name' => 'Iphone','description' => 'Iphone','created_at' => '2022-01-01 00:00:00','category_id' => 'GADGET','price' => 27000]);
    DB::table('product')->insert(['id' => '5','name' => 'Mie Ayam','description' => 'Mie Ayam','created_at' => '2022-01-01 00:00:00','category_id' => 'FOOD','price' => 50]);
  }

  public function testAgregate() {
    $this->insertProductWithPrice();

    $collection = DB::table('product')->count("id");
    self::assertEquals(5,$collection);

    $collection = DB::table('product')->min("id");
    self::assertEquals(1,$collection);


    // $collection->each(function ($item) {
    //   Log::info(json_encode($item));;
    // });
  }

  public function testQueryBuilderRow() {
    $this->insertProductWithPrice();

   $collection = DB::table("product") ->select(
      DB::raw('count(*) as total'),
      DB::raw('min(price) as min_price'),
      DB::raw('max(price) as max_price')
    )->get();
    self::assertEquals(5, $collection[0]->total);
    self::assertEquals(50, $collection[0]->min_price);
    self::assertEquals(160000, $collection[0]->max_price);
    }

    public function insertProductFood() {
      DB::table('product')->insert(['id' => '6','name' => 'Mie Ayam Bakar','description' => 'Mie Ayam Bakar','created_at' => '2022-01-01 00:00:00','category_id' => 'FOOD','price' => 12000]);
      DB::table('product')->insert(['id' => '7','name' => 'Bakso','description' => 'Bakso','created_at' => '2022-01-01 00:00:00','category_id' => 'FOOD','price' => 20000]);
    }

    public function testGroupBy () {
      $this->insertProductWithPrice();
      $this->insertProductFood();

      $collection = DB::table('product')->select('category_id', DB::raw('count(*) as total'))->groupBy('category_id')
      ->orderBy('category_id', 'desc')
      ->get();
      self::assertEquals(1, $collection[0]->total);
      self::assertEquals(2, $collection[1]->total);
      self::assertEquals('LAPTOP', $collection[0]->category_id);
    }
    public function testGroupByHaving () {
      $this->insertProductWithPrice();
      $this->insertProductFood();

      $collection = DB::table('product')->select('category_id', DB::raw('count(*) as total'))->groupBy('category_id')
      ->having(DB::raw('count(*)'), '>', 3)
      ->orderBy('category_id', 'desc')
      ->get();
      self::assertCount(0, $collection);
    }


    public function testLocking() {
      $this->insertProductWithPrice();
      DB::transaction(function () {
         $collection = DB::table('product')    
          ->where('id', '=', '1')
          ->lockForUpdate()
          ->get();
        self::assertCount(1, $collection);
      });
    }

    public function testPaginate() {
      $this->insertCategories();

      $paginate = DB::table('categories')->paginate(perPage: 2, page: 1);
      self::assertCount(2, $paginate);
      self::assertEquals(2, $paginate->perPage());
      self::assertEquals(1, $paginate->currentPage());
      self::assertEquals(3, $paginate->lastPage());
      self::assertEquals(5, $paginate->total());

      $collection = $paginate->items();
      foreach($collection as $item) {
        Log::info(json_encode($item));
      }
    }

    public function testIterationPaginate() {
      $this->insertCategories();
      $page = 1;
      while(true){
        $paginate = DB::table('categories')->paginate(perPage: 2, page: $page);

        if ($paginate->isEmpty()) {
          break;
        } else {
          $page++;
          $collection = $paginate->items();
          self::assertLessThanOrEqual(2, count($collection));
          foreach($collection as $item) {
          Log::info(json_encode($item));
          }
        }
      }
    }

    public function testCursorPaginate() {
      $this->insertCategories();

      $cursor = 'id';
      while(true) {
        $paginate = DB::table("categories")->orderBy('id')->cursorPaginate(perPage: 2, cursor: $cursor);

        foreach($paginate as $item) {
          self::assertNotNull($item);
          Log::info(json_encode($item));
        }
        

        $cursor = $paginate->nextCursor();
        if ($cursor === null) {
          break;
        }
        
      }
    }
    
}
