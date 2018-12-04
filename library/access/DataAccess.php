<?php
namespace tools\access;

interface DataAccess {
    public function getCounts();
    public function addCount($name);
    public function findMember();
    public function addMember();
}
