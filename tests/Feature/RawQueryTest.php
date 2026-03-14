<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RawQueryTest extends TestCase
{
  use RefreshDatabase;  
  protected function setUp(): void
  {
    parent::setUp();
    DB::statement("create table if not exists categories (
        id varchar(100) not null primary key,
        name varchar(100) not null,
        description text,
        created_at timestamp
    )");
    DB::delete("delete from categories");
  } 

  public function testCrud() 
  {
    DB::insert("insert into categories (id, name, description,created_at) values (?, ?, ?, ?)",
    ['GADGET', 'Gadget', 'Gadget category', '2022-01-01 00:00:00']);

    $result = DB::select("select * from categories where id = ?", ['GADGET']);

    self::assertCount(1, $result);
    self::assertEquals('GADGET', $result[0]->id);
    self::assertEquals('Gadget', $result[0]->name);
    self::assertEquals('Gadget category', $result[0]->description);
    self::assertEquals('2022-01-01 00:00:00', $result[0]->created_at);
  }
}
