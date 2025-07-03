<?php

namespace Core;

/**
 * QueryProfiler - A simple tool for profiling SQL queries
 * 
 * This class can be integrated with the Model class to track 
 * and profile database queries for performance optimization.
 */
class QueryProfiler
{
    /**
     * @var array Stores logged queries with timing information
     */
    private static $queries = [];
    
    /**
     * @var bool Whether the profiler is enabled
     */
    private static $enabled = false;
    
    /**
     * @var int Maximum number of queries to log
     */
    private static $maxQueries = 100;
    
    /**
     * Enable the query profiler
     */
    public static function enable()
    {
        self::$enabled = true;
    }
    
    /**
     * Disable the query profiler
     */
    public static function disable()
    {
        self::$enabled = false;
    }
    
    /**
     * Check if the profiler is enabled
     * 
     * @return bool
     */
    public static function isEnabled()
    {
        return self::$enabled;
    }
    
    /**
     * Log a query with timing information
     * 
     * @param string $sql The SQL query
     * @param array $params Query parameters
     * @param float $startTime Start time of the query
     * @param float $endTime End time of the query
     * @param int $rowCount Number of affected rows (for write queries)
     * @param string $source Optional source information (file and line)
     */
    public static function logQuery($sql, $params = [], $startTime = null, $endTime = null, $rowCount = null, $source = null)
    {
        if (!self::$enabled) {
            return;
        }
        
        // Generate backtrace if source not provided
        if ($source === null) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $caller = isset($backtrace[1]) ? $backtrace[1] : $backtrace[0];
            $source = (isset($caller['file']) ? basename($caller['file']) : 'unknown') . 
                      (isset($caller['line']) ? ':' . $caller['line'] : '');
        }
        
        // Calculate duration if times provided
        $duration = null;
        if ($startTime !== null && $endTime !== null) {
            $duration = round(($endTime - $startTime) * 1000, 2); // Duration in milliseconds
        }
        
        // Limit array size to prevent memory issues
        if (count(self::$queries) >= self::$maxQueries) {
            array_shift(self::$queries);
        }
        
        self::$queries[] = [
            'sql' => $sql,
            'params' => $params,
            'duration' => $duration,
            'row_count' => $rowCount,
            'source' => $source,
            'timestamp' => microtime(true)
        ];
    }
    
    /**
     * Get all logged queries
     * 
     * @return array
     */
    public static function getQueries()
    {
        return self::$queries;
    }
    
    /**
     * Clear all logged queries
     */
    public static function clear()
    {
        self::$queries = [];
    }
    
    /**
     * Get the total duration of all queries
     * 
     * @return float Total duration in milliseconds
     */
    public static function getTotalDuration()
    {
        $total = 0;
        foreach (self::$queries as $query) {
            if (isset($query['duration'])) {
                $total += $query['duration'];
            }
        }
        return $total;
    }
    
    /**
     * Get the average duration of all queries
     * 
     * @return float Average duration in milliseconds
     */
    public static function getAverageDuration()
    {
        $count = count(self::$queries);
        if ($count === 0) {
            return 0;
        }
        return self::getTotalDuration() / $count;
    }
    
    /**
     * Get the slowest query
     * 
     * @return array|null The slowest query or null if no queries logged
     */
    public static function getSlowestQuery()
    {
        if (empty(self::$queries)) {
            return null;
        }
        
        $slowest = null;
        $maxDuration = -1;
        
        foreach (self::$queries as $query) {
            if (isset($query['duration']) && $query['duration'] > $maxDuration) {
                $maxDuration = $query['duration'];
                $slowest = $query;
            }
        }
        
        return $slowest;
    }
    
    /**
     * Render a summary table of the queries
     * 
     * @return string HTML table with query details
     */
    public static function renderSummary()
    {
        $totalQueries = count(self::$queries);
        $totalDuration = self::getTotalDuration();
        $avgDuration = self::getAverageDuration();
        $slowestQuery = self::getSlowestQuery();
        
        $html = '
        <div class="query-profiler" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; font-family: Arial, sans-serif;">
            <h3 style="margin-top: 0;">Query Profiler Summary</h3>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                <tr>
                    <th style="text-align: left; padding: 8px; border-bottom: 1px solid #dee2e6;">Total Queries</th>
                    <td style="text-align: right; padding: 8px; border-bottom: 1px solid #dee2e6;">' . $totalQueries . '</td>
                </tr>
                <tr>
                    <th style="text-align: left; padding: 8px; border-bottom: 1px solid #dee2e6;">Total Duration</th>
                    <td style="text-align: right; padding: 8px; border-bottom: 1px solid #dee2e6;">' . $totalDuration . ' ms</td>
                </tr>
                <tr>
                    <th style="text-align: left; padding: 8px; border-bottom: 1px solid #dee2e6;">Average Query Time</th>
                    <td style="text-align: right; padding: 8px; border-bottom: 1px solid #dee2e6;">' . $avgDuration . ' ms</td>
                </tr>';
        
        if ($slowestQuery) {
            $html .= '
                <tr>
                    <th style="text-align: left; padding: 8px; border-bottom: 1px solid #dee2e6;">Slowest Query</th>
                    <td style="text-align: right; padding: 8px; border-bottom: 1px solid #dee2e6;">' . $slowestQuery['duration'] . ' ms</td>
                </tr>
                <tr>
                    <th style="text-align: left; padding: 8px; border-bottom: 1px solid #dee2e6;">Slowest Source</th>
                    <td style="text-align: right; padding: 8px; border-bottom: 1px solid #dee2e6;">' . $slowestQuery['source'] . '</td>
                </tr>';
        }
        
        $html .= '
            </table>';
            
        if ($totalQueries > 0) {
            $html .= '
            <details>
                <summary style="cursor: pointer; padding: 8px; background-color: #e9ecef; border-radius: 4px;">View All Queries</summary>
                <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="text-align: left; padding: 8px; background-color: #e9ecef; border-bottom: 1px solid #dee2e6;">#</th>
                            <th style="text-align: left; padding: 8px; background-color: #e9ecef; border-bottom: 1px solid #dee2e6;">Query</th>
                            <th style="text-align: left; padding: 8px; background-color: #e9ecef; border-bottom: 1px solid #dee2e6;">Duration</th>
                            <th style="text-align: left; padding: 8px; background-color: #e9ecef; border-bottom: 1px solid #dee2e6;">Source</th>
                        </tr>
                    </thead>
                    <tbody>';
            
            foreach (self::$queries as $i => $query) {
                // Add color coding based on query duration
                $rowColor = '';
                if (isset($query['duration'])) {
                    if ($query['duration'] > 100) {
                        $rowColor = '#ffdddd'; // Red for slow queries
                    } else if ($query['duration'] > 50) {
                        $rowColor = '#ffffdd'; // Yellow for medium queries
                    }
                }
                
                $html .= '
                        <tr style="background-color: ' . $rowColor . '">
                            <td style="text-align: left; padding: 8px; border-bottom: 1px solid #dee2e6;">' . ($i + 1) . '</td>
                            <td style="text-align: left; padding: 8px; border-bottom: 1px solid #dee2e6; font-family: monospace;">' . 
                                htmlspecialchars($query['sql']) . 
                                (empty($query['params']) ? '' : '<br><small style="color: #666;">Params: ' . htmlspecialchars(json_encode($query['params'])) . '</small>') . 
                            '</td>
                            <td style="text-align: left; padding: 8px; border-bottom: 1px solid #dee2e6;">' . 
                                (isset($query['duration']) ? $query['duration'] . ' ms' : 'N/A') . 
                            '</td>
                            <td style="text-align: left; padding: 8px; border-bottom: 1px solid #dee2e6;">' . 
                                $query['source'] . 
                            '</td>
                        </tr>';
            }
            
            $html .= '
                    </tbody>
                </table>
            </details>';
        }
        
        $html .= '
        </div>';
        
        return $html;
    }
} 