/**
 * @license Copyright (c) 2014-2023, CKSource Holding sp. z o.o. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */
import { ClassicEditor } from '@ckeditor/ckeditor5-editor-classic';
import { Alignment } from '@ckeditor/ckeditor5-alignment';
import { Autoformat } from '@ckeditor/ckeditor5-autoformat';
import { Bold, Italic, Subscript, Superscript } from '@ckeditor/ckeditor5-basic-styles';
import { BlockQuote } from '@ckeditor/ckeditor5-block-quote';
import { CloudServices } from '@ckeditor/ckeditor5-cloud-services';
import { Essentials } from '@ckeditor/ckeditor5-essentials';
import { FontColor, FontFamily, FontSize } from '@ckeditor/ckeditor5-font';
import { Heading } from '@ckeditor/ckeditor5-heading';
import { HorizontalLine } from '@ckeditor/ckeditor5-horizontal-line';
import { HtmlEmbed } from '@ckeditor/ckeditor5-html-embed';
import { Image, ImageInsert, ImageResize, ImageToolbar, ImageUpload } from '@ckeditor/ckeditor5-image';
import { Indent } from '@ckeditor/ckeditor5-indent';
import { AutoLink, Link } from '@ckeditor/ckeditor5-link';
import { List } from '@ckeditor/ckeditor5-list';
import { MediaEmbed } from '@ckeditor/ckeditor5-media-embed';
import { Paragraph } from '@ckeditor/ckeditor5-paragraph';
import { PasteFromOffice } from '@ckeditor/ckeditor5-paste-from-office';
import { SourceEditing } from '@ckeditor/ckeditor5-source-editing';
import { Table, TableCellProperties, TableColumnResize, TableToolbar } from '@ckeditor/ckeditor5-table';
import { TextTransformation } from '@ckeditor/ckeditor5-typing';
import { Base64UploadAdapter } from '@ckeditor/ckeditor5-upload';
import { WordCount } from '@ckeditor/ckeditor5-word-count';
declare class Editor extends ClassicEditor {
    static builtinPlugins: (typeof Alignment | typeof AutoLink | typeof Autoformat | typeof Base64UploadAdapter | typeof BlockQuote | typeof Bold | typeof CloudServices | typeof Essentials | typeof FontColor | typeof FontFamily | typeof FontSize | typeof Heading | typeof HorizontalLine | typeof HtmlEmbed | typeof Image | typeof ImageInsert | typeof ImageResize | typeof ImageToolbar | typeof ImageUpload | typeof Indent | typeof Italic | typeof Link | typeof List | typeof MediaEmbed | typeof Paragraph | typeof PasteFromOffice | typeof SourceEditing | typeof Subscript | typeof Superscript | typeof Table | typeof TableCellProperties | typeof TableColumnResize | typeof TableToolbar | typeof TextTransformation | typeof WordCount)[];
    static defaultConfig: {
        toolbar: {
            items: string[];
        };
        language: string;
        image: {
            toolbar: string[];
        };
        table: {
            contentToolbar: string[];
        };
    };
}
export default Editor;
