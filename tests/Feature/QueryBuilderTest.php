<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class QueryBuilderTest extends TestCase
{
  use RefreshDatabase;  
  protected function setUp(): void
  {
    parent::setUp();
    DB::statement("create table if not exists categories (
        id varchar(100) not null primary key,
        name varchar(100) not null
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
}
