<?php
function isBrowser() {
    // 获取用户代理字符串
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    // 简单的浏览器判断，可以根据需要更改或扩展
    if (preg_match('/(MSIE|Trident|Edge|Firefox|Chrome|Safari|Opera)/i', $userAgent)) {
        return true; // 是浏览器
    }

    return false; // 不是浏览器
}

// 如果不是浏览器访问，跳转到指定的网址
if (!isBrowser()) {
    header("Location: https://cn.bing.com/search?q=%E8%AF%B7%E4%BD%BF%E7%94%A8%E6%B5%8F%E8%A7%88%E5%99%A8%E6%89%93%E5%BC%80");
    exit();
}

// 默认搜索关键词
$search_query = isset($_GET['search']) ? urlencode($_GET['search']) : '';

// 请求电影列表 接口来自baiapi.cn
$search_results = [];
if ($search_query) {
    $search_url = "https://baiapi.cn/api/ysss?wd={$search_query}";
    $search_data = @file_get_contents($search_url);
    if ($search_data) {
        $search_results = json_decode($search_data, true);
    }
}

// 获取影片详情 接口来自baiapi.cn
$movie_details = null;
if (isset($_GET['movie_id'])) {
    $details_url = "https://baiapi.cn/api/ysss?id=" . urlencode($_GET['movie_id']);
    $details_data = @file_get_contents($details_url);
    if ($details_data) {
        $movie_details = json_decode($details_data, true);
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>影视搜索</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            background-image: url(), url(https://vpsaz.cn/qita/tupian/bj.svg);
            background-position: right bottom, left top;
            background-repeat: no-repeat, repeat;
        }
        #container {
            max-width: 800px;
            width: 100%;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        #searchForm {
            text-align: center;
            margin-bottom: 20px;
        }
        #searchForm input[type="text"] {
            width: 70%;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        #searchForm button {
            padding: 8px 15px;
            font-size: 16px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #movieList, #movieDetails {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        #movieList button {
            display: block;
            width: 100%;
            margin: 5px 0;
            padding: 10px;
            text-align: left;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            color: #333;
        }
        #movieList button:hover {
            background-color: #e9ecef;
        }
        #movieDetails img {
            width: 200px;
            height: auto;
            margin-top: 10px;
            display: block;
            border-radius: 8px;
        }
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            white-space: nowrap;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        table td {
            background-color: #fafafa;
        }
        #movieDetails .details {
            max-height: 300px;
            overflow-x: auto;
            overflow-y: auto;
            margin-top: 10px;
        }
        .movie-info {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .movie-info .details {
            width: 70%;
        }
        .movie-info .poster {
            width: 28%;
        }
        .movie-info .content {
            width: 100%;
            margin-top: 20px;
        }
        #movieDetails .play-button {
            display: inline-block;
            margin: 5px;
            padding: 8px 12px;
            font-size: 16px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        #movieDetails .play-button:hover {
            background-color: #218838;
        }
        @media (max-width: 768px) {
            #searchForm input[type="text"] {
                width: 50%;
            }
            .movie-info {
                flex-direction: column;
            }
            .movie-info .details {
                width: 100%;
            }
            .movie-info .poster {
                width: 100%;
                text-align: center;
            }
            table th, table td {
                font-size: 14px;
            }
            #movieList button {
                font-size: 14px;
            }
        }
        @media (max-width: 480px) {
            #searchForm input[type="text"] {
                width: 50%;
            }
            .movie-info .details {
                font-size: 14px;
            }
            table th, table td {
                font-size: 12px;
            }
            #movieList button {
                font-size: 14px;
            }
        }
        hr {
            border: 0;
            height: 1px;
            background: #ddd;
            margin: 20px 0;
            position: relative;
        }
        hr::before {
            content: "";
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 2px;
            background-color: #007bff;
            border-radius: 2px;
        }

        /* 背景遮罩层样式 */
        #announcementModal {
            display: none; /* 默认隐藏 */
            position: fixed;
            top: 0; /* 背景从顶部开始 */
            left: 0; /* 背景从左侧开始 */
            width: 100%; /* 背景宽度为100% */
            height: 100%; /* 背景高度为100% */
            background-color: rgba(0, 0, 0, 0.5); /* 半透明背景 */
            z-index: 9999; /* 保证遮罩层位于最上层 */
        }

        /* 公告窗口样式 */
        .modal-content {
            position: absolute; /* 设置为绝对定位 */
            top: 50%; /* 顶部距离视口的50% */
            left: 50%; /* 左侧距离视口的50% */
            transform: translate(-50%, -50%); /* 完全居中 */
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            max-width: 600px; /* 最大宽度 */
            width: 80%; /* 默认宽度 */
            text-align: left; /* 内容左对齐 */
        }

        .modal-content h2 {
            margin-bottom: 20px;
        }

        /* 按钮样式 */
        #closeButton {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            margin-left: auto;  /* 使按钮右对齐 */
            display: block;     /* 使按钮作为块级元素显示 */
        }

        #closeButton:hover {
            background-color: #0056b3;
        }

        /* 倒计时显示在按钮 */
        .countdown {
            font-size: 18px;
            color: #fff;
            margin-left: 10px;
        }

    /* 假设 LA-DATA-WIDGET 生成的 widget 是一个 div */
    #LA-DATA-WIDGET {
        display: block; /* 将元素转为块级元素 */
        margin: 0 auto; /* 自动左右边距实现居中 */
        text-align: center; /* 如果内部有文字内容，居中 */
    }
    </style>
</head>
<body>

    <!-- 背景遮罩层 -->
    <div id="announcementModal">
        <div class="modal-content">
            <h2>📢 免责声明</h2>
            <p>本站所有内容均来自互联网，本站不会保存、复制或传播任何视频文件，也不对本站上的任何内容负法律责任。如果本站部分内容侵犯您的版权请告知，在必要证明文件下我们第一时间撤除。</p>
            <p><font color="red"><b>请勿相信视频中的任何广告！</b></font></p>
            <button id="closeButton" disabled>
                <span id="countdownText">5</span> 秒后可关闭
            </button>
        </div>
    </div>

    <div id="container">
        <h1>影视搜索</h1>

        <!-- 搜索表单 -->
        <div id="searchForm">
            <form action="" method="get">
                <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="请输入影片名称">
                <button type="submit">搜索</button>
            </form>
        </div>

        <!-- 搜索结果展示 -->
        <?php if (!isset($_GET['movie_id']) && isset($search_results['list']) && count($search_results['list']) > 0): ?>
            <div id="movieList">
                <h3>🔍 搜索结果</h3>
                <?php foreach ($search_results['list'] as $movie): ?>
                    <form action="" method="get">
                        <input type="hidden" name="movie_id" value="<?php echo htmlspecialchars($movie['vod_id']); ?>">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($_GET['search']); ?>">
                        <button type="submit"><?php echo htmlspecialchars($movie['vod_name']) . ' - ' . htmlspecialchars($movie['vod_remarks']); ?></button>
                    </form>
                <?php endforeach; ?>
            </div>
        <?php elseif (isset($_GET['movie_id']) && $movie_details && isset($movie_details['name'])): ?>
            <!-- 影片详情展示 -->
            <div id="movieDetails">
                <h3>🎬 影片详情</h3><hr>
                <div class="movie-info">
                    <div class="details">
                        <table>
                            <tr><th>导演</th><td><?php echo htmlspecialchars($movie_details['director']); ?></td></tr>
                            <tr><th>类型</th><td><?php echo htmlspecialchars($movie_details['class']); ?></td></tr>
                            <tr><th>日期</th><td><?php echo htmlspecialchars($movie_details['pubdate']); ?></td></tr>
                            <tr><th>评分</th><td><?php echo htmlspecialchars($movie_details['douban_score']); ?></td></tr>
                            <tr><th>地区</th><td><?php echo htmlspecialchars($movie_details['area']); ?></td></tr>
                        </table>
                    </div>
                    <div class="poster">
                        <img src="<?php echo htmlspecialchars($movie_details['pic']); ?>" alt="<?php echo htmlspecialchars($movie_details['name']); ?>" style="max-width: 100%;">
                    </div>
                </div>

                <div class="content">
                    <h3>💬 影片简介</h3><hr><p><?php echo $movie_details['content']; ?></p>
                </div><br>

                <h3>🔞 播放列表</h3><hr>
                <div>
                    <?php if (isset($movie_details['play_url']) && is_array($movie_details['play_url'])): ?>
                        <?php foreach ($movie_details['play_url'] as $episode): ?>
                            <a href="https://baiapi.cn/api/webbfq?apiKey=313a67206aa9feded2fe4f97f0d06781&url=<?php echo htmlspecialchars($episode['link']); ?>" class="play-button" target="_blank">
                                <?php echo htmlspecialchars($episode['title']); ?>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>暂无播放列表。</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- 公告组成部分 -->
    <script>
        // 检查是否已经显示过公告
        function shouldShowAnnouncement() {
            const lastShownDate = localStorage.getItem('lastShownDate');
            const today = new Date().toLocaleDateString();

            // 如果日期不同，则需要显示公告
            if (lastShownDate !== today) {
                return true;
            }
            return false;
        }

        // 显示公告
        function showAnnouncement() {
            const modal = document.getElementById('announcementModal');
            const closeButton = document.getElementById('closeButton');
            const countdownText = document.getElementById('countdownText');
            let countdown = 5; // 倒计时5秒

            modal.style.display = 'block'; // 显示公告窗口

            // 设置倒计时
            const timer = setInterval(function() {
                countdown--;
                countdownText.textContent = countdown;

                // 在倒计时期间，按钮显示 "X 秒后可关闭"
                if (countdown > 0) {
                    closeButton.disabled = true;
                    closeButton.textContent = `${countdown} 秒后可关闭`;
                }

                // 倒计时结束，按钮显示 "关闭公告"
                if (countdown <= 0) {
                    clearInterval(timer);
                    closeButton.disabled = false; // 启用关闭按钮
                    closeButton.textContent = '关闭公告'; // 显示关闭按钮
                }
            }, 1000);

            // 点击关闭按钮时，记录今天已经显示过公告
            closeButton.onclick = function() {
                // 记录今天已经显示过公告
                localStorage.setItem('lastShownDate', new Date().toLocaleDateString());

                // 关闭公告窗口
                modal.style.display = 'none'; 
            };
        }

        // 页面加载时，检查是否需要显示公告
        window.onload = function() {
            if (shouldShowAnnouncement()) {
                showAnnouncement();
            }
        };
    </script>
</body>
</html>
