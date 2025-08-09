#!/usr/bin/env python3
"""
Project Context Generator for Claude AI
Generates a comprehensive but compact context file from any codebase
that can be shared with Claude to quickly get up to speed on a project.

Cross-platform compatible: Linux, macOS, Windows
"""

import os
import sys
import json
import hashlib
import mimetypes
from datetime import datetime
from pathlib import Path
import argparse
import fnmatch
import platform

class ProjectContextGenerator:
    def __init__(self, root_path=".", output_file="claude_context.md"):
        # Path handling that works across all operating systems
        self.root_path = Path(root_path).expanduser().resolve()
        self.output_file = output_file
        self.is_windows = platform.system() == 'Windows'
        self.is_mac = platform.system() == 'Darwin'
        self.is_linux = platform.system() == 'Linux'
        
        # File patterns to always ignore
        self.ignore_patterns = [
            '*.pyc', '*.pyo', '*.pyd', '__pycache__',
            '*.so', '*.dylib', '*.dll', '*.exe',
            '*.zip', '*.tar', '*.gz', '*.rar', '*.7z',
            '*.jpg', '*.jpeg', '*.png', '*.gif', '*.ico', '*.svg',
            '*.mp3', '*.mp4', '*.avi', '*.mov', '*.wav',
            '*.pdf', '*.doc', '*.docx', '*.xls', '*.xlsx',
            '.DS_Store', 'Thumbs.db', '*.log',
            '*.min.js', '*.min.css', '*.map',
            'package-lock.json', 'yarn.lock', 'composer.lock',
            '*.woff', '*.woff2', '*.ttf', '*.eot',
            '.env', '.env.*', '*.key', '*.pem', '*.cert'
        ]
        
        # Directories to skip (cross-platform)
        self.skip_dirs = {
            '.git', '.svn', '.hg', 'node_modules', 'vendor', 
            'dist', 'build', 'out', '.next', '.nuxt', 'coverage',
            'tmp', 'temp', 'cache', '.cache', 'logs',
            '__pycache__', '.pytest_cache', '.vscode', '.idea',
            'venv', 'env', '.venv', 'virtualenv',
            # Windows specific
            'bin', 'obj', '.vs',
            # Mac specific  
            '.Spotlight-V100', '.Trashes'
        }
        
        # Important files to always include if they exist
        self.priority_files = [
            'README.md', 'README.txt', 'README',
            'package.json', 'composer.json', 'requirements.txt',
            'Gemfile', 'Cargo.toml', 'go.mod', 'pom.xml',
            '.gitignore', 'Dockerfile', 'docker-compose.yml',
            'Makefile', 'webpack.config.js', 'tsconfig.json'
        ]
        
        # File extensions to include full content (small, important files)
        self.full_content_extensions = {
            '.md', '.txt', '.json', '.yml', '.yaml', '.toml',
            '.ini', '.cfg', '.conf', '.sh', '.bash',
            '.gitignore', '.dockerignore', '.env.example'
        }
        
        # Code file extensions to analyze
        self.code_extensions = {
            '.py', '.js', '.ts', '.jsx', '.tsx', '.php',
            '.java', '.c', '.cpp', '.cs', '.go', '.rs',
            '.rb', '.swift', '.kt', '.scala', '.r',
            '.vue', '.svelte', '.html', '.css', '.scss', '.sass'
        }
        
        self.context_data = {
            'files': {},
            'structure': {},
            'stats': {},
            'key_files': {}
        }

    def should_ignore(self, file_path):
        """Check if file should be ignored"""
        name = file_path.name
        
        # Check against ignore patterns
        for pattern in self.ignore_patterns:
            if fnmatch.fnmatch(name, pattern):
                return True
        
        # Check file size (skip files over 1MB)
        try:
            if file_path.stat().st_size > 1024 * 1024:
                return True
        except:
            pass
            
        return False

    def read_file_with_encoding(self, file_path):
        """Try to read a file with multiple encoding attempts"""
        encodings = ['utf-8', 'latin-1', 'cp1252', 'iso-8859-1']
        
        for encoding in encodings:
            try:
                with open(file_path, 'r', encoding=encoding) as f:
                    return f.read()
            except (UnicodeDecodeError, UnicodeError):
                continue
        
        # If all encodings fail, try with error handling
        try:
            with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
                return f.read()
        except Exception:
            return None

    def get_file_summary(self, file_path):
        """Generate a smart summary of a file"""
        try:
            ext = file_path.suffix.lower()
            size = file_path.stat().st_size
            
            # Try to read the file content
            content = self.read_file_with_encoding(file_path)
            
            if content is None:
                return {'type': 'binary', 'size': size}
            
            lines = content.splitlines()
            
            summary = {
                'lines': len(lines),
                'size': size,
                'type': ext[1:] if ext else 'text'
            }
            
            # For configuration files, include full content if small
            if ext in self.full_content_extensions and size < 10000:
                summary['content'] = content
                
            # For code files, extract structure
            elif ext in self.code_extensions:
                summary.update(self.analyze_code_file(content, ext))
                
            return summary
            
        except Exception as e:
            return {'error': str(e)}

    def analyze_code_file(self, content, ext):
        """Extract important information from code files"""
        analysis = {}
        lines = content.splitlines()
        
        # Extract imports/includes
        imports = []
        classes = []
        functions = []
        components = []
        exports = []
        
        for line in lines[:100]:  # Check first 100 lines for imports
            line = line.strip()
            
            # Python imports
            if ext == '.py':
                if line.startswith('import ') or line.startswith('from '):
                    imports.append(line)
                elif line.startswith('class '):
                    classes.append(line.split('(')[0].replace('class ', ''))
                elif line.startswith('def '):
                    functions.append(line.split('(')[0].replace('def ', ''))
            
            # JavaScript/TypeScript
            elif ext in ['.js', '.ts', '.jsx', '.tsx']:
                if line.startswith('import ') or line.startswith('const ') and ' require(' in line:
                    imports.append(line[:80])
                elif line.startswith('export '):
                    exports.append(line[:80])
                elif 'class ' in line and '{' in line:
                    classes.append(line.split('{')[0].strip())
                elif line.startswith('function ') or 'const ' in line and ' = (' in line:
                    functions.append(line.split('(')[0].strip())
            
            # PHP
            elif ext == '.php':
                if line.startswith('use ') or line.startswith('require') or line.startswith('include'):
                    imports.append(line)
                elif line.startswith('class '):
                    classes.append(line.split('{')[0].strip())
                elif line.startswith('function '):
                    functions.append(line.split('(')[0].strip())
        
        # Add header comment if exists (often contains important info)
        header_comment = []
        in_comment = False
        for line in lines[:30]:
            if '/**' in line or '/*' in line:
                in_comment = True
            if in_comment:
                header_comment.append(line)
                if '*/' in line:
                    break
        
        if imports:
            analysis['imports'] = imports[:10]  # Limit to first 10
        if classes:
            analysis['classes'] = classes[:10]
        if functions:
            analysis['functions'] = functions[:15]
        if exports:
            analysis['exports'] = exports[:10]
        if header_comment and len(header_comment) > 1:
            analysis['header'] = '\n'.join(header_comment[:10])
            
        return analysis

    def build_tree_structure(self, path, prefix="", max_depth=3, current_depth=0):
        """Build a tree structure of the project"""
        if current_depth >= max_depth:
            return "..."
        
        items = []
        try:
            # Use sorted with explicit key for consistent cross-platform behavior
            entries = sorted(
                path.iterdir(), 
                key=lambda x: (not x.is_dir(), x.name.lower())
            )
            
            for i, entry in enumerate(entries):
                # Skip hidden files on Unix-like systems unless specified
                if not self.is_windows and entry.name.startswith('.') and entry.name not in self.priority_files:
                    if entry.is_dir() and entry.name not in ['.git', '.github']:
                        continue
                
                if entry.name in self.skip_dirs:
                    continue
                if entry.is_file() and self.should_ignore(entry):
                    continue
                    
                is_last = i == len(entries) - 1
                current_prefix = "└── " if is_last else "├── "
                next_prefix = prefix + ("    " if is_last else "│   ")
                
                if entry.is_dir():
                    items.append(f"{prefix}{current_prefix}{entry.name}/")
                    subtree = self.build_tree_structure(entry, next_prefix, max_depth, current_depth + 1)
                    if subtree and subtree != "...":
                        items.append(subtree)
                else:
                    items.append(f"{prefix}{current_prefix}{entry.name}")
        except (PermissionError, OSError) as e:
            # Handle permission errors gracefully (common on Windows)
            items.append(f"{prefix}[Permission Denied]")
            
        return "\n".join(items)

    def generate_context(self):
        """Generate the complete context"""
        print(f"Analyzing project at: {self.root_path}")
        print(f"Running on: {platform.system()} {platform.release()}")
        
        # Get project structure
        tree = self.build_tree_structure(self.root_path)
        
        # Analyze files
        all_files = []
        code_files = {}
        config_files = {}
        doc_files = {}
        
        # Use rglob with explicit pattern for better cross-platform behavior
        for file_path in self.root_path.rglob('*'):
            if file_path.is_file():
                try:
                    rel_path = file_path.relative_to(self.root_path)
                    
                    # Convert to forward slashes for consistency across platforms
                    rel_path_str = str(rel_path).replace('\\', '/')
                    
                    # Skip if in ignored directory
                    if any(part in self.skip_dirs for part in rel_path.parts):
                        continue
                    
                    if not self.should_ignore(file_path):
                        all_files.append(rel_path_str)
                        summary = self.get_file_summary(file_path)
                        
                        # Categorize files
                        if file_path.suffix in self.code_extensions:
                            code_files[rel_path_str] = summary
                        elif file_path.suffix in self.full_content_extensions:
                            config_files[rel_path_str] = summary
                        elif file_path.suffix in ['.md', '.txt', '.rst']:
                            doc_files[rel_path_str] = summary
                except (PermissionError, OSError):
                    # Skip files we can't access (common on Windows)
                    continue
        
        # Generate statistics
        stats = {
            'total_files': len(all_files),
            'code_files': len(code_files),
            'config_files': len(config_files),
            'doc_files': len(doc_files),
            'languages': {}
        }
        
        # Count languages
        for file in code_files:
            ext = Path(file).suffix
            stats['languages'][ext] = stats['languages'].get(ext, 0) + 1
        
        # Build the context document
        self.write_context(tree, stats, config_files, code_files, doc_files, all_files)
        
        print(f"Context file generated: {self.output_file}")
        print(f"Total files analyzed: {stats['total_files']}")
        print(f"Output size: {Path(self.output_file).stat().st_size / 1024:.1f} KB")

    def write_context(self, tree, stats, config_files, code_files, doc_files, all_files):
        """Write the context to a markdown file"""
        with open(self.output_file, 'w', encoding='utf-8') as f:
            # Header
            f.write(f"# Project Context for Claude AI\n\n")
            f.write(f"Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
            f.write(f"Project Path: `{self.root_path}`\n\n")
            
            # Project Statistics
            f.write("## Project Statistics\n\n")
            f.write(f"- Total Files: {stats['total_files']}\n")
            f.write(f"- Code Files: {stats['code_files']}\n")
            f.write(f"- Config Files: {stats['config_files']}\n")
            f.write(f"- Documentation Files: {stats['doc_files']}\n\n")
            
            if stats['languages']:
                f.write("### Languages/Technologies\n")
                for ext, count in sorted(stats['languages'].items(), key=lambda x: x[1], reverse=True):
                    f.write(f"- `{ext}`: {count} files\n")
                f.write("\n")
            
            # Project Structure
            f.write("## Project Structure\n\n")
            f.write("```\n")
            f.write(f"{self.root_path.name}/\n")
            f.write(tree)
            f.write("\n```\n\n")
            
            # Key Configuration Files
            if config_files:
                f.write("## Configuration Files\n\n")
                for file, summary in list(config_files.items())[:10]:  # Limit to 10 most important
                    if 'content' in summary:
                        f.write(f"### {file}\n")
                        f.write("```" + Path(file).suffix[1:] + "\n")
                        f.write(summary['content'][:2000])  # Limit content length
                        if len(summary['content']) > 2000:
                            f.write("\n... (truncated)")
                        f.write("\n```\n\n")
            
            # Code Structure Summary
            f.write("## Code Structure Summary\n\n")
            
            # Group by directory
            dirs = {}
            for file, summary in code_files.items():
                dir_name = str(Path(file).parent)
                if dir_name not in dirs:
                    dirs[dir_name] = []
                dirs[dir_name].append((file, summary))
            
            for dir_name in sorted(dirs.keys())[:20]:  # Limit to 20 directories
                files = dirs[dir_name]
                if files:
                    f.write(f"### {dir_name}/\n\n")
                    for file, summary in files[:10]:  # Limit files per directory
                        f.write(f"**{Path(file).name}**")
                        
                        if 'lines' in summary:
                            f.write(f" ({summary['lines']} lines)")
                        f.write("\n")
                        
                        if 'classes' in summary and summary['classes']:
                            f.write(f"- Classes: {', '.join(summary['classes'][:5])}\n")
                        if 'functions' in summary and summary['functions']:
                            f.write(f"- Functions: {', '.join(summary['functions'][:5])}\n")
                        if 'imports' in summary and summary['imports']:
                            f.write(f"- Key imports: {len(summary['imports'])} imports\n")
                        if 'exports' in summary and summary['exports']:
                            f.write(f"- Exports: {len(summary['exports'])} exports\n")
                        
                        f.write("\n")
            
            # Documentation Files
            if doc_files:
                f.write("## Documentation\n\n")
                for file, summary in list(doc_files.items())[:5]:
                    f.write(f"- **{file}**: {summary.get('lines', '?')} lines\n")
                f.write("\n")
            
            # Instructions for Claude
            f.write("## Context for Claude AI\n\n")
            f.write("This is a snapshot of the project structure and key files. ")
            f.write("The project appears to be a ")
            
            # Detect project type
            project_types = []
            if 'package.json' in [f for f in all_files]:
                project_types.append("Node.js/JavaScript")
            if 'composer.json' in [f for f in all_files]:
                project_types.append("PHP")
            if 'requirements.txt' in [f for f in all_files] or '.py' in stats['languages']:
                project_types.append("Python")
            if any(f.endswith('.php') for f in code_files):
                if 'wordpress' in str(self.root_path).lower() or any('wp-' in f for f in all_files):
                    project_types.append("WordPress Plugin/Theme")
            
            if project_types:
                f.write(f"{'/'.join(project_types)} project.\n\n")
            else:
                f.write("software project.\n\n")
            
            f.write("When working with this project, please note:\n")
            f.write("1. This is a summary - not all files are shown\n")
            f.write("2. Large files have been truncated\n")
            f.write("3. Binary files and dependencies are excluded\n")
            f.write("4. Focus on the structure and key configuration files\n")

def main():
    parser = argparse.ArgumentParser(
        description='Generate project context for Claude AI',
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  %(prog)s                     # Analyze current directory
  %(prog)s ~/projects/myapp   # Analyze specific directory  
  %(prog)s -o context.md       # Custom output file
  
Compatible with Linux, macOS, and Windows.
        """
    )
    parser.add_argument('path', nargs='?', default='.', help='Project path (default: current directory)')
    parser.add_argument('-o', '--output', default='claude_context.md', help='Output file name')
    parser.add_argument('--include-hidden', action='store_true', help='Include hidden files')
    parser.add_argument('--version', action='version', version='%(prog)s 1.0.0')
    
    args = parser.parse_args()
    
    # Validate path exists
    if not Path(args.path).exists():
        print(f"Error: Path '{args.path}' does not exist", file=sys.stderr)
        sys.exit(1)
    
    generator = ProjectContextGenerator(args.path, args.output)
    
    try:
        generator.generate_context()
    except KeyboardInterrupt:
        print("\nOperation cancelled by user")
        sys.exit(0)
    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == "__main__":
    main()