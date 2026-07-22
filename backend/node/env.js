import dotenv from 'dotenv';
import { fileURLToPath } from 'url';
import path from 'path';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const dotenvPath = path.join(__dirname, '.env');

dotenv.config({ path: dotenvPath });
process.env.DOTENV_CONFIG_PATH = dotenvPath;

export { dotenvPath };
