<?php

namespace Tests\Feature;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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
    DB::delete("delete from categories");
  } 
  public function testInsert() {
    DB::table('categories')->insert([
      'id' => 'GADGET',
      'name' => 'Gadget',
    ]);
    DB::table('categories')->insert([
      'id' => 'FOOD',
      'name' => 'Food',
    ]);

    $result = DB::select('SELECT COUNT(id) as total FROM categories');

    self::assertEquals(2, $result[0]->total);
  }

  public function testSelect() {
    $this->testInsert();

    $collection = DB::table('categories')->select('id', 'name')->get();
    $this->assertCount(2, $collection);
    
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function insertCategories() {
    DB::table('categories')
    ->insert(['id' => 'FOOD', 'name' => 'Food', 'created_at' => '2022-01-01 00:00:00']);
    DB::table('categories')
    ->insert(['id' => 'FASION', 'name' => 'Fasion', 'created_at' => '2022-01-01 00:00:00']);
    DB::table('categories')
    ->insert(['id' => 'LAPTOP', 'name' => 'Laptop', 'created_at' => '2022-01-01 00:00:00']);
    DB::table('categories')
    ->insert(['id' => 'KATOK', 'name' => 'Katok', 'created_at' => '2022-01-01 00:00:00']);
    DB::table('categories')
    ->insert(['id' => 'MEJA', 'name' => 'Meja', 'created_at' => '2022-01-01 00:00:00']);
  }

  public function testWhere() {
    $this->insertCategories();

    $collection = DB::table('categories')->where(function(Builder $builder) {
      $builder->where('id', '=', 'FASION');
      $builder->orWhere('id', '=', 'LAPTOP');
    })->get();
    $this->assertCount(2, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function testWhereBetween() {
    $this->testWhere();

    $collection = DB::table('categories')->whereBetween('created_at', ['2022-01-01 00:00:00', '2022-01-03 00:00:00'])->get();
    $this->assertCount(5, $collection);
    $collection->each(function ($item) {
      Log::info(json_encode($item));
    });
  }

  public function testWhereIn() {
    $this->insertCategories();

    $collection = DB::table('categories')->whereIn('id', ['FOOD', 'FASION'])->get();
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
  
}
