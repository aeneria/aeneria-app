<?php
namespace AppBundle\FeedObject;

interface FeedObject {
    public function fetchData(\Datetime $date);
}
