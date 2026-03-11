import pg from 'pg';

const { Pool } = pg;

export interface DbConfig {
  host: string;
  port: number;
  database: string;
  user: string;
  password: string;
}

let pool: pg.Pool | null = null;
let useMcp: boolean = process.env.USE_MCP_DB === 'true';

export const setUseMcp = (enabled: boolean): void => {
  useMcp = enabled;
};

export const getDbPool = (): pg.Pool => {
  if (useMcp) {
    throw new Error('MCP database mode is enabled. Use McpDatabase class instead of direct pool access.');
  }
  
  if (!pool) {
    const config: DbConfig = {
      host: process.env.DB_HOST || '127.0.0.1',
      port: parseInt(process.env.DB_PORT || '5432'),
      database: process.env.DB_DATABASE || 'lms2025_test',
      user: process.env.DB_USERNAME || 'laravel',
      password: process.env.DB_PASSWORD || 'secret',
    };

    pool = new Pool({
      ...config,
      max: 10,
      idleTimeoutMillis: 30000,
      connectionTimeoutMillis: 10000,
    });

    pool.on('error', (err: Error) => {
      console.error('Unexpected database error:', err);
    });
  }
  return pool;
};

export class McpDatabase {
  private mcpClient: any = null;
  
  async connect(): Promise<void> {
    // In a real implementation, this would connect to the MCP PostgreSQL server
    // For now, we'll use the direct pg connection as fallback
    console.log('🔌 Connecting to database via MCP...');
    // await this.mcpClient.connect();
  }
  
  async query<T = unknown>(text: string, params?: unknown[]): Promise<T[]> {
    if (useMcp) {
      // Execute via MCP
      console.log(`🗄️  MCP Query: ${text}`);
      // const result = await this.mcpClient.execute({ query: text, parameters: params });
      // return result.rows as T[];
      
      // Fallback to direct query for now
      return this.directQuery<T>(text, params);
    } else {
      return this.directQuery<T>(text, params);
    }
  }
  
  private async directQuery<T = unknown>(text: string, params?: unknown[]): Promise<T[]> {
    const pool = getDbPool();
    const result = await pool.query(text, params);
    return result.rows as T[];
  }
  
  async execute(text: string, params?: unknown[]): Promise<pg.QueryResult> {
    if (useMcp) {
      console.log(`🗄️  MCP Execute: ${text}`);
      // return await this.mcpClient.execute({ query: text, parameters: params });
      const pool = getDbPool();
      return pool.query(text, params);
    }
    const pool = getDbPool();
    return pool.query(text, params);
  }
  
  async disconnect(): Promise<void> {
    if (this.mcpClient) {
      // await this.mcpClient.disconnect();
      this.mcpClient = null;
    }
  }
}

export const mcpDb = new McpDatabase();

export const query = async <T = unknown>(text: string, params?: unknown[]): Promise<T[]> => {
  if (useMcp) {
    return mcpDb.query<T>(text, params);
  }
  const pool = getDbPool();
  const result = await pool.query(text, params);
  return result.rows as T[];
};

export const execute = async (text: string, params?: unknown[]): Promise<pg.QueryResult> => {
  if (useMcp) {
    return mcpDb.execute(text, params);
  }
  const pool = getDbPool();
  return pool.query(text, params);
};

export const truncateTables = async (tables: string[]): Promise<void> => {
  if (useMcp) {
    for (const table of tables) {
      await mcpDb.execute(`TRUNCATE TABLE ${table} RESTART IDENTITY CASCADE`);
    }
  } else {
    const pool = getDbPool();
    const tableList = tables.join(', ');
    await pool.query(`TRUNCATE TABLE ${tableList} RESTART IDENTITY CASCADE`);
  }
};

export const closeDb = async (): Promise<void> => {
  if (useMcp) {
    await mcpDb.disconnect();
  } else if (pool) {
    await pool.end();
    pool = null;
  }
};

export const verifyRecord = async <T = Record<string, unknown>>(
  table: string,
  id: number
): Promise<T | null> => {
  const result = await query<T>(`SELECT * FROM ${table} WHERE id = $1`, [id]);
  return result[0] || null;
};

export const verifyRecordByField = async <T = Record<string, unknown>>(
  table: string,
  field: string,
  value: unknown
): Promise<T | null> => {
  const result = await query<T>(`SELECT * FROM ${table} WHERE ${field} = $1`, [value]);
  return result[0] || null;
};

export const verifyFieldValue = async (
  table: string,
  id: number,
  field: string,
  expectedValue: unknown
): Promise<boolean> => {
  const result = await query<Record<string, unknown>>(
    `SELECT ${field} FROM ${table} WHERE id = $1`,
    [id]
  );
  
  if (!result[0]) return false;
  
  const actualValue = result[0][field];
  
  if (actualValue instanceof Date && expectedValue instanceof Date) {
    return actualValue.getTime() === expectedValue.getTime();
  }
  
  return actualValue === expectedValue;
};

export const recordExists = async (table: string, id: number): Promise<boolean> => {
  const result = await query<{ exists: boolean }>(
    'SELECT EXISTS(SELECT 1 FROM $1 WHERE id = $2) as exists',
    [table, id]
  );
  return result[0]?.exists || false;
};

export const countRecords = async (table: string, where?: string): Promise<number> => {
  const queryText = where 
    ? `SELECT COUNT(*) as count FROM ${table} WHERE ${where}`
    : `SELECT COUNT(*) as count FROM ${table}`;
  
  const result = await query<{ count: string }>(queryText);
  return parseInt(result[0]?.count || '0', 10);
};
