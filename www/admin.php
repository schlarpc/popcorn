<?php
    require 'admin-auth.php';
    require 'shoebox.class.php';
    require 'admin-controls.php';

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Admin Panel</title>
        <link href="css/bootstrap.css" rel="stylesheet" media="screen">
        <style type="text/css">
            html {
                min-height: 100%;
            }
            body {
                height: 100%;
                background-color: rgb(238, 238, 238);
            }
            .hero-unit {
                height: 100%;
                margin: 0;
            }
            .corner {
                position: absolute;
                top: 10px;
                right: 18px;
            }
        </style>
    </head>

    <body>

    <div class="corner">
        <a class="btn btn-warning" id="restart">Reboot Server</a>
    </div>

    <div class="hero-unit">
        <h1>Popcorn <small>Projection Booth</small></h1>
        <h2>Available Files</h2>
        <table class="table table-striped table-framed">
            <thead>
                <tr>
                    <th>Filename</th>
                    <th>Size</th>
                    <th>Controls</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    function human_filesize($bytes, $decimals = 2) {
                        $size = array('B','kB','MB','GB','TB','PB','EB','ZB','YB');
                        $factor = floor((strlen($bytes) - 1) / 3);
                        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
                    }
                    
                    $handle = opendir('/var/videos');
                    while (false !== ($entry = readdir($handle))) {
                        $path = "/var/videos/$entry";
                        if (is_file($path) && $entry !== ".htaccess") {
                        ?>
                        <tr>
                            <td class="file"><?=htmlspecialchars($entry)?></td>
                            <td><?=human_filesize(filesize($path))?></td>
                            <td><span class="label label-success"><a style="color: #FFF; text-decoration: none;" href="#" class="play">Play</a></span>&nbsp;<span class="label label-important"><a style="color: #FFF; text-decoration: none;" href="#"  class="delete">Delete</a></span></td>
                        </tr>
                        <?php
                        }
                    }
                ?>
            </tbody>
        </table>

        <h2>Shoebox Library</h2>
        <form class="form-horizontal" action="#">
            <div class="control-group">
                <input type="text" placeholder="Search" id="shoebox-search" />
                <label class="radio inline"><input type="radio" name="shoebox-mode" value="movie" checked /> Movies</label>
                <label class="radio inline"><input type="radio" name="shoebox-mode" value="tv" /> TV</label>
            </div>
        </form>
        <table class="table table-striped table-framed">
            <tbody id="shoebox-results">
            </tbody>
        </table>
        
        <h2>Now Playing:</h2>
        <h3 id="nowplaying" style="margin-bottom: -12px;"><?=htmlspecialchars(file_get_contents('/tmp/nowplaying'))?></h3>
        <span class="label label-success"><a style="color: #FFF; text-decoration: none;" href="#" id="resume">Play</a></span>&nbsp;<span class="label label-warning"><a style="color: #FFF; text-decoration: none;" href="#" id="pause">Pause</a></span>&nbsp;<span class="label label-important"><a style="color: #FFF; text-decoration: none;" href="#" id="stop">Stop</a></span>

        <h2>Download</h2>
        <form action="#">
            <input id="url" type="text" placeholder="URL"/>
            <select id="type" name="type">
                <option value="direct">Direct</option>
                <option value="site">YouTube, Vimeo, etc</option>
            </select>
            <br />
            <button id="download" class="btn btn-primary btn-large" data-loading-text="<i class='icon-download icon-white'></i> Downloading...">Download</button>
        </form>

    </div>
    <script src="js/jquery-2.0.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    
    <script>
        $(function() {
            var shoebox_movie = null;
            var shoebox_tv = null;
        
            $('#download').click(function() {
                $('#download').button('loading').attr('disabled', 'disabled');
                $.post('admin.php', {
                    'command': 'download',
                    'url': $('#url').val(),
                    'type': $('#type option:selected').val(),
                },
                function() { window.location.reload(); });
                return false;
            });
            
            $('#restart').click(function() {
                $.post('admin.php', {
                    'command': 'restart',
                });
                return false;
            });
            
            $('#stop').click(function() {
                $.post('admin.php', {
                    'command': 'stop',
                });
                return false;
            });
            
            $('#resume').click(function() {
                $.post('admin.php', {
                    'command': 'resume',
                });
                return false;
            });
            
            $('#pause').click(function() {
                $.post('admin.php', {
                    'command': 'pause',
                });
                return false;
            });
            
            $('.play').mousedown(function(event) {
                var file = $(this).parent().parent().parent().find('.file').text();
                $.post('admin.php', {
                    'command': 'play',
                    'file': file,
                    'offset': (event.which == 1 ? 0 : prompt('Offset?')),
                });
                $('#nowplaying').text(file);
                return false;
            });
            
            $('.delete').click(function() {
                var file = $(this).parent().parent().parent().find('.file').text();
                $.post('admin.php', {
                    'command': 'delete',
                    'file': file,
                },
                function() { window.location.reload(); });
                return false;
            });
            
            
            $('#shoebox-search').keyup(function(e){
                if(e.keyCode == 13)
                {
                    processSearch();
                }
            });
            
            function processSearch() {
                var type = $('input[name=shoebox-mode]:checked').val();

                if (type == 'tv') {
                    if (shoebox_tv === null) {
                        $.post('admin.php', {
                            'command': 'shoebox-list',
                            'type': 'tv',
                        }, function(data) {
                            shoebox_tv = $.parseJSON(data);
                            searchTV($('#shoebox-search').val());
                        })
                    } else {
                        searchTV($('#shoebox-search').val());
                    }
                } else if (type == 'movie') {
                    if (shoebox_movie === null) {
                        $.post('admin.php', {
                            'command': 'shoebox-list',
                            'type': 'movie',
                        }, function(data) {
                            shoebox_movie = $.parseJSON(data);
                            searchMovie($('#shoebox-search').val());
                        })
                    } else {
                        searchMovie($('#shoebox-search').val());
                    }
                }
            }

            function searchTV(text) {
                console.log(text);
            }

            function searchMovie(text) {
                $('#shoebox-results').html('');
                for (var i = 0; i < shoebox_movie.length; i++) {
                    if (shoebox_movie[i].title.toUpperCase().indexOf(text.toUpperCase()) !== -1) {
                        $('#shoebox-results').append('<tr><td class="file">' + shoebox_movie[i].title + '</td><td><span class="label label-success"><a style="color: #FFF; text-decoration: none;" href="#" class="play-shoebox-movie" data-id="' + shoebox_movie[i].id + '">Play</a></span></td></tr>');
                    }
                }
            }

            $('#shoebox-results').on('click', '.play-shoebox-movie', function() {
                $.post('admin.php', {
                    'command': 'shoebox-play-movie',
                    'id': $(this).data('id'),
                });
            });
            
            $('form').submit(function() {
                return false;
            });
        });
    </script>
    </body>
</html>

