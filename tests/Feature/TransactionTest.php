<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TransactionTest extends TestCase
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

  public function testTransaction() {
    DB::transaction(function () {
      DB::insert("insert into categories (id, name, description,created_at) values (?, ?, ?, ?)",
      ['GADGET', 'Gadget', 'Gadget category', '2022-01-01 00:00:00']);
      DB::insert("insert into categories (id, name, description,created_at) values (?, ?, ?, ?)",
      ['FOOD', 'Food', 'food category', '2022-01-01 00:00:00']);
    });
    $result = DB::select("select * from categories");
    self::assertCount(2, $result);
  }

  public function testTransactionFailed() {
    try{
      DB::transaction(function () {
      DB::insert("insert into categories (id, name, description,created_at) values (?, ?, ?, ?)",
      ['GADGET', 'Gadget', 'Gadget category', '2022-01-01 00:00:00']);
      DB::insert("insert into categories (id, name, description,created_at) values (?, ?, ?, ?)",
      ['GADGET', 'Gadget', 'Gadget category', '2022-01-01 00:00:00']);
    });
    }catch(\Illuminate\Database\QueryException $e){
      //expected
    }
    $result = DB::select("select * from categories");
    self::assertCount(0, $result);
  }

  public function testManualTransactionSuccess() {
    try{
      DB::beginTransaction();
      DB::insert("insert into categories (id, name, description,created_at) values (?, ?, ?, ?)",
      ['GADGET', 'Gadget', 'Gadget category', '2022-01-01 00:00:00']);
      DB::insert("insert into categories (id, name, description,created_at) values (?, ?, ?, ?)",
      ['FOOD', 'Food', 'food category', '2022-01-03 00:00:00']);
      DB::commit();
      
    }catch(\Illuminate\Database\QueryException $e){
      //expected
    }
    $result = DB::select("select * from categories");
    self::assertCount(2, $result);
  }
  public function testManualTransactionFailed() {
    try{
      DB::beginTransaction();
      DB::insert("insert into categories (id, name, description,created_at) values (?, ?, ?, ?)",
      ['GADGET', 'Gadget', 'Gadget category', '2022-01-01 00:00:00']);
      DB::insert("insert into categories (id, name, description,created_at) values (?, ?, ?, ?)",
      ['GADGET', 'Gadget', 'Gadget category', '2022-01-01 00:00:00']);
      DB::commit();
      
    }catch(\Illuminate\Database\QueryException $e){
      DB::rollBack();
    }
    $result = DB::select("select * from categories");
    self::assertCount(0, $result);
  }
}
