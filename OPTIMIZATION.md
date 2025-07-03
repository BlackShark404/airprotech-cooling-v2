# Database Optimization & Query Profiling

This document explains the database optimization and query profiling tools available in the AirProTech project.

## Database Optimization

The project includes several database optimization techniques:

### 1. Database Indexes

We've added strategic indexes to improve query performance on commonly accessed columns and relationships:

- Foreign key indexes for faster joins
- Partial indexes for filtered queries (e.g., active records only)
- Composite indexes for multi-column conditions
- Functional indexes for specialized query patterns

### 2. Running the Optimization Script

To apply all database optimizations:

```bash
php script/optimize_database.php
```

This script will run all SQL statements in `config/db_optimizations.sql`, which includes creating indexes and updating statistics.

### 3. JSON Aggregation Queries

The application now uses PostgreSQL's JSON aggregation functions to optimize data retrieval:

- Product details with features, specs, variants, and inventory are fetched in a single query
- This eliminates N+1 query issues previously present in the product management area

## Query Profiling

We've added a query profiling system to help identify and fix slow queries:

### 1. How to Enable Query Profiling

Query profiling is automatically enabled in development environments. The system detects if you're working locally and activates the profiler.

To manually enable/disable:

```php
// Enable
Core\QueryProfiler::enable();

// Disable
Core\QueryProfiler::disable();
```

### 2. Viewing Query Profile Data

When enabled, the query profiler displays a summary at the bottom of admin pages, showing:

- Total number of queries executed
- Total query execution time
- Average query duration
- Slowest query and its source
- Detailed list of all queries with execution times

### 3. Reading Profile Results

The profiler color-codes query results:
- Green: Fast queries (<50ms)
- Yellow: Medium-speed queries (50-100ms)
- Red: Slow queries (>100ms)

Focus on optimizing the red queries first for the biggest performance gains.

## Additional Database Monitoring Tools

For more detailed database analysis, consider using these tools:

### GUI Tools for PostgreSQL on Mac

1. **TablePlus** (https://tableplus.com/)
   - Modern, native database manager
   - Query execution plans
   - Performance analysis

2. **Postico** (https://eggerapps.at/postico/)
   - Simple, user-friendly interface
   - Query history

3. **pgAdmin 4**
   - Free and open-source
   - Comprehensive analysis tools
   - Query plans and monitoring

### Advanced Profiling

For more advanced profiling, consider:

1. **pg_stat_statements Extension**
   - Enable in PostgreSQL to track query statistics
   - Shows aggregate query performance

2. **EXPLAIN ANALYZE**
   - Use `EXPLAIN ANALYZE` before SQL queries to see execution plans
   - Helps identify missing indexes or inefficient joins

## Best Practices

1. Always check the query profiler when developing new features
2. Use JSON aggregation to avoid N+1 query problems
3. Add indexes for any new fields used in WHERE, JOIN, or ORDER BY clauses
4. Keep the optimization SQL file updated when adding new tables or query patterns 