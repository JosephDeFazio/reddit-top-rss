<?php

// Functions
include "functions.php";


// Get requested subreddit
// If none is specified, set a default
if(isset($data['subreddit'])) {
	$subreddit = $data['subreddit'];
} elseif(isset($_GET["subreddit"])) {
	$subreddit = strip_tags(trim($_GET["subreddit"]));
} else {
	$subreddit = DEFAULT_SUBREDDIT;
}


// Set default threshold score
$thresholdScore = 0;


// Average posts per day
if(((isset($data['filterType']) && $data['filterType'] == 'postsPerDay') || (isset($_GET["averagePostsPerDay"]) && $_GET["averagePostsPerDay"]))) {
	$thresholdPostsPerDay = isset($data['filterType']) && $data['filterType'] == 'postsPerDay' ? $data['averagePostsPerDay'] : $_GET["averagePostsPerDay"];
	$jsonFeedFileTopMonth = getFile("https://www.reddit.com/r/" . $subreddit . "/top/.json?t=month&limit=1", "redditJSON", "cache/reddit/$subreddit-top-?t=month&limit=1.json", 60 * 5);
	$jsonFeedFileTopMonthParsed = json_decode($jsonFeedFileTopMonth, true);
	$jsonFeedFileTopMonthScore = $jsonFeedFileTopMonthParsed["data"]["children"][0]["data"]["score"];
	if($thresholdPostsPerDay <= 3) {
		$jsonFeedFileTop = getFile("https://www.reddit.com/r/" . $subreddit . "/top/.json?t=month&limit=" . $thresholdPostsPerDay * 30, "redditJSON", "cache/reddit/$subreddit-top-?t=month&limit=" . $thresholdPostsPerDay * 30 . ".json", 60 * 5);
	} elseif($thresholdPostsPerDay <= 14) {
		$jsonFeedFileTop = getFile("https://www.reddit.com/r/" . $subreddit . "/top/.json?t=week&limit=" . $thresholdPostsPerDay * 7, "redditJSON", "cache/reddit/$subreddit-top-?t=week&limit=" . $thresholdPostsPerDay * 7 . ".json", 60 * 5);
	} else {
		$jsonFeedFileTop = getFile("https://www.reddit.com/r/" . $subreddit . "/top/.json?t=day&limit=" . $thresholdPostsPerDay, "redditJSON", "cache/reddit/$subreddit-top-?t=day&limit=" . $thresholdPostsPerDay . ".json", 60 * 5);
	}
	$jsonFeedFileTopParsed = json_decode($jsonFeedFileTop, true);
	$jsonFeedFileTopItems = $jsonFeedFileTopParsed["data"]["children"];
	usort($jsonFeedFileTopItems, "sortByScoreDesc");
	$jsonFeedFileTopParsedScore = $jsonFeedFileTopParsed["data"]["children"][0]["data"]["score"];
	$scoreMultiplier = $jsonFeedFileTopMonthScore / $jsonFeedFileTopParsedScore;
	$thresholdScore = round(array_values(array_slice($jsonFeedFileTopItems, -1))[0]["data"]["score"] * $scoreMultiplier);
}


// Threshold percentage
if(((isset($data['filterType']) && $data['filterType'] == 'percentage') || (isset($_GET["threshold"]) && $_GET["threshold"]))) {
	$thresholdPercentage = isset($data['filterType']) && $data['filterType'] == 'percentage' ? $data['threshold'] : $_GET["threshold"];
	$totalFeedScore = 0;
	$jsonFeedFileTopMonth = getFile("https://www.reddit.com/r/" . $subreddit . "/top/.json?t=month&limit=100", "redditJSON", "cache/reddit/$subreddit-top-?t=month&limit=100.json", 60 * 5);
	$jsonFeedFileTopMonthParsed = json_decode($jsonFeedFileTopMonth, true);
	$jsonFeedFileTopMonthItems = $jsonFeedFileTopMonthParsed["data"]["children"];
		foreach ($jsonFeedFileTopMonthItems as $feedItem) {
		$totalFeedScore += $feedItem["data"]["score"];
	}
	$averageFeedScore = $totalFeedScore/count($jsonFeedFileTopMonthItems);
	$thresholdScore = floor($averageFeedScore * $thresholdPercentage/100);
}


// Threshold score
if((isset($data['filterType']) && $data['filterType'] == 'score') || (isset($_GET["score"]) && $_GET["score"])) {
	$thresholdScore = isset($data['filterType']) && $data['filterType'] == 'score' ? $data['score'] : $_GET["score"];
}


// Sort by Created Date
function sortByCreatedDate($a, $b) {
	if ($a["data"]["created_utc"] > $b["data"]["created_utc"]) {
		return 1;
	} else if ($a["data"]["created_utc"] < $b["data"]["created_utc"]) {
		return -1;
	} else {
		return 0;
	}
}


// Sort by Score
function sortByScoreDesc($a, $b) {
	if ($a["data"]["score"] < $b["data"]["score"]) {
		return 1;
	} else if ($a["data"]["score"] > $b["data"]["score"]) {
		return -1;
	} else {
		return 0;
	}
}