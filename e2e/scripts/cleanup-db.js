#!/usr/bin/env node

const { Pool } = require('pg');

const config = {
  host: process.env.DB_HOST || '127.0.0.1',
  port: parseInt(process.env.DB_PORT || '5432'),
  database: process.env.DB_DATABASE || 'lms2025_db',
  user: process.env.DB_USERNAME || 'lms',
  password: process.env.DB_PASSWORD || 'lms',
};

const tables = [
  'users',
  'courses',
  'groups',
  'categories',
  'user_courses',
  'group_user',
  'progress',
  'topics',
  'favorites',
  'questions',
  'gift',
];

async function cleanup() {
  const pool = new Pool(config);
  
  try {
    console.log('Connecting to database...');
    await pool.connect();
    
    console.log('Truncating tables...');
    await pool.query(`
      TRUNCATE TABLE ${tables.join(', ')} RESTART IDENTITY CASCADE
    `);
    
    console.log('Tables truncated successfully');
    
    const result = await pool.query(`
      SELECT table_name 
      FROM information_schema.tables 
      WHERE table_schema = 'public'
      AND table_type = 'BASE TABLE'
    `);
    
    console.log('\nCurrent table row counts:');
    for (const row of result.rows) {
      const countResult = await pool.query(
        `SELECT COUNT(*) as count FROM ${row.table_name}`
      );
      console.log(`  ${row.table_name}: ${countResult.rows[0].count}`);
    }
    
  } catch (error) {
    console.error('Error:', error.message);
    process.exit(1);
  } finally {
    await pool.end();
  }
}

cleanup();
