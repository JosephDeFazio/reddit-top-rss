<?php

// Clear cache
array_map("unlink", glob("cache/mercury/*.*"));
array_map("unlink", glob("cache/reddit/*.*"));
array_map("unlink", glob("cache/rss/*.*"));
array_map("unlink", glob("cache/tmp/*.*"));
opcache_reset();