<?php
namespace Verse;

interface Paginable {
    public function getItems($page, $rpp);
    public function getTotalRecords();
}
