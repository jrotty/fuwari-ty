
<h1 align="center"> OpenOlah Theme Fuwari  </h1>

---

<div align="center">  

一款 [OpenOlah](https://github.com/openolah/openolah) 的博客主题  
移植于 Halo [halo-theme-fuwari](https://github.com/jiewenhuang/halo-theme-fuwari) 主题

</div>

## 项目结构

```
fuwari/
├── src/                    # TypeScript 源代码
│   ├── alpine-data/        # Alpine.js 数据组件
│   ├── constants/          # 常量定义
│   ├── preact/             # Preact 组件
│   ├── styles/             # 样式文件 (CSS, Stylus)
│   ├── types/              # TypeScript 类型定义
│   ├── utils/              # 工具函数
│   ├── widgets/            # UI 组件
│   └── main.ts             # 入口文件
├── templates/              # Go 模板文件
│   ├── assets/             # 静态资源
│   │   ├── dist/           # 构建输出目录
│   │   └── images/         # 图片资源
│   ├── modules/            # 模板模块
│   │   ├── widgets/        # 小部件模板
│   │   └── ...
│   └── *.html              # 页面模板
├── i18n/                   # 国际化文件
├── package.json            # Node.js 依赖
├── vite.config.ts          # Vite 构建配置
├── tsconfig.json           # TypeScript 配置
├── tailwind.config.js      # Tailwind CSS 配置
├── postcss.config.js       # PostCSS 配置
├── theme.yaml              # 主题元数据
├── settings.yaml           # 主题设置
└── build.sh                # 构建脚本
```

## 开发环境要求

- Node.js >= 18
- pnpm >= 8

## 构建步骤

### 1. 安装依赖

```bash
cd data/themes/fuwari
pnpm install
```

### 2. 开发模式

```bash
pnpm dev
```

开发服务器将在 `http://localhost:5173` 启动。

### 3. 生产构建

```bash
pnpm build
# 或者使用构建脚本
./build.sh
```

构建产物将输出到 `templates/assets/dist/` 目录。

## 技术栈

- **前端框架**: Preact
- **构建工具**: Vite
- **CSS 框架**: Tailwind CSS
- **样式预处理**: Stylus
- **交互框架**: Alpine.js
- **页面路由**: Swup
- **图片查看**: PhotoSwipe
- **滚动条**: OverlayScrollbars

## 模板转换说明

原版 Halo 主题使用 Thymeleaf 模板引擎，本版本已转换为 Go 模板语法：

| Thymeleaf | Go Template |
|-----------|-------------|
| `${variable}` | `{{ .Variable }}` |
| `th:if="${condition}"` | `{{ if .Condition }}` |
| `th:each="item : ${items}"` | `{{ range .Items }}` |
| `th:replace="~{module}"` | `{{ template "module" . }}` |
| `th:text="${text}"` | `{{ .Text }}` |

## 许可证

GPL-3.0 License

## 致谢

- [Halo](https://halo.run)
- [Fuwari](https://github.com/saicaca/fuwari)
- [halo-theme-fuwari](https://github.com/jiewenhuang/halo-theme-fuwari)
