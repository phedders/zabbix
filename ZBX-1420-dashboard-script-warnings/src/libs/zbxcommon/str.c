/*
** ZABBIX
** Copyright (C) 2000-2005 SIA Zabbix
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
**/

#include "common.h"
#include "threads.h"

/******************************************************************************
 *                                                                            *
 * Function: app_title                                                        *
 *                                                                            *
 * Purpose: print title of application on stdout                              *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 * Comments:  title_message - is global variable which must be initialized    *
 *                            in each zabbix application                      *
 *                                                                            *
 ******************************************************************************/
static void app_title()
{
	printf("%s v%s (revision %s) (%s)\n", title_message,
			ZABBIX_VERSION, ZABBIX_REVISION, ZABBIX_REVDATE);
}

/******************************************************************************
 *                                                                            *
 * Function: version                                                          *
 *                                                                            *
 * Purpose: print version and compilation time of application on stdout       *
 *          by application request with parameter '-v'                        *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 ******************************************************************************/
void version()
{
	app_title();
	printf("Compilation time:  %s %s\n", __DATE__, __TIME__);
}

/******************************************************************************
 *                                                                            *
 * Function: usage                                                            *
 *                                                                            *
 * Purpose: print application parameters on stdout                            *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 * Comments:  usage_message - is global variable which must be initialized    *
 *                            in each zabbix application                      *
 *                                                                            *
 ******************************************************************************/
void usage()
{
	printf("usage: %s %s\n", progname, usage_message);
}

/******************************************************************************
 *                                                                            *
 * Function: help                                                             *
 *                                                                            *
 * Purpose: print help of application parameters on stdout by application     *
 *          request with parameter '-h'                                       *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 * Comments:  help_message - is global variable which must be initialized     *
 *                            in each zabbix application                      *
 *                                                                            *
 ******************************************************************************/
void help()
{
	char **p = help_message;

	app_title();
	printf("\n");
	usage();
	printf("\n");
	while (*p) printf("%s\n", *p++);
}

/******************************************************************************
 *                                                                            *
 * Function: find_char                                                        *
 *                                                                            *
 * Purpose: locate a character in the string                                  *
 *                                                                            *
 * Parameters: str - string                                                   *
 *             c   - character to find                                        *
 *                                                                            *
 * Return value:  position of the character                                   *
 *                FAIL - otherwise                                            *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 * Comments: !!! beter use system functions like 'strchr' !!!                 *
 *                                                                            *
 ******************************************************************************/
int	find_char(char *str,char c)
{
	char *p;
	for(p = str; *p; p++)
		if(*p == c) return (int)(p - str);

	return	FAIL;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_error                                                        *
 *                                                                            *
 * Purpose: Print error text to the stderr                                    *
 *                                                                            *
 * Parameters: fmt - format of mesage                                         *
 *                                                                            *
 * Return value:                                                              *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 ******************************************************************************/
/* #define ZBX_STDERR_FILE "zbx_errors.log" */

void __zbx_zbx_error(const char *fmt, ...)
{
	va_list args;
	FILE *f = NULL;

#if defined(ZBX_STDERR_FILE)
	f = fopen(ZBX_STDERR_FILE,"a+");
#else
	f = stderr;
#endif /* ZBX_STDERR_FILE */

	va_start(args, fmt);

	fprintf(f, "%s [%li]: ",progname, zbx_get_thread_id());
	vfprintf(f, fmt, args);
	fprintf(f, "\n");
	fflush(f);

	va_end(args);

#if defined(ZBX_STDERR_FILE)
	zbx_fclose(f);
#endif /* ZBX_STDERR_FILE */
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_snprintf                                                     *
 *                                                                            *
 * Purpose: Sequire version of snprintf function.                             *
 *          Add zero character at the end of string.                          *
 *                                                                            *
 * Parameters: str - destination buffer poiner                                *
 *             count - size of destination buffer                             *
 *             fmt - format                                                   *
 *                                                                            *
 * Return value:                                                              *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 ******************************************************************************/
int __zbx_zbx_snprintf(char* str, size_t count, const char *fmt, ...)
{
	va_list	args;
	int	writen_len = 0;

	assert(str);

	va_start(args, fmt);

	writen_len = vsnprintf(str, count, fmt, args);
	writen_len = MIN(writen_len, ((int)count) - 1);
	writen_len = MAX(writen_len, 0);

	str[writen_len] = '\0';

	va_end(args);

	return writen_len;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_vsnprintf                                                    *
 *                                                                            *
 * Purpose: Sequire version of vsnprintf function.                            *
 *          Add zero character at the end of string.                          *
 *                                                                            *
 * Parameters: str - destination buffer poiner                                *
 *             count - size of destination buffer                             *
 *             fmt - format                                                   *
 *                                                                            *
 * Return value:                                                              *
 *                                                                            *
 * Author: Alexei Vladishev (see also zbx_snprintf)                           *
 *                                                                            *
 ******************************************************************************/
int zbx_vsnprintf(char* str, size_t count, const char *fmt, va_list args)
{
	int	writen_len = 0;

	assert(str);

	writen_len = vsnprintf(str, count, fmt, args);
	writen_len = MIN(writen_len, ((int)count) - 1);
	writen_len = MAX(writen_len, 0);

	str[writen_len] = '\0';

	return writen_len;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_snprintf_alloc                                               *
 *                                                                            *
 * Purpose: Sequire version of snprintf function.                             *
 *          Add zero character at the end of string.                          *
 *          Reallocs memory if not enough.                                    *
 *                                                                            *
 * Parameters: str - destination buffer pointer                               *
 *             alloc_len - already allocated memory                           *
 *             offset - offset for writing                                    *
 *             max_len - fmt + data won't write more than max_len bytes       *
 *             fmt - format                                                   *
 *                                                                            *
 * Return value:                                                              *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 ******************************************************************************/
void __zbx_zbx_snprintf_alloc(char **str, int *alloc_len, int *offset, int max_len, const char *fmt, ...)
{
	va_list	args;

	assert(str);
	assert(*str);

	assert(alloc_len);
	assert(offset);

	assert(fmt);

	va_start(args, fmt);

	if (*offset + max_len >= *alloc_len) {
		while (*offset + max_len >= *alloc_len)
			*alloc_len *= 2;
		*str = zbx_realloc(*str, *alloc_len);
	}

	*offset += zbx_vsnprintf(*str+*offset, max_len, fmt, args);

	va_end(args);
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_strcpy_alloc                                                 *
 *                                                                            *
 * Purpose: Add zero character at the end of string.                          *
 *          Reallocs memory if not enough.                                    *
 *                                                                            *
 * Parameters: str - destination buffer pointer                               *
 *             alloc_len - already allocated memory                           *
 *             offset - offset for writing                                    *
 *             src - copied null terminated string                            *
 *                                                                            *
 * Return value:                                                              *
 *                                                                            *
 * Author: Aleksander Vladishev                                               *
 *                                                                            *
 ******************************************************************************/
void	zbx_strcpy_alloc(char **str, int *alloc_len, int *offset, const char *src)
{
	int	sz;

	assert(str && *str);
	assert(alloc_len);
	assert(offset);
	assert(src);

	sz = (int)strlen(src);

	if (*offset + sz >= *alloc_len) {
		*alloc_len += sz < 32 ? 64 : 2 * sz;
		*str = zbx_realloc(*str, *alloc_len);
	}

	memcpy(*str + *offset, src, sz);
	*offset += sz;
	(*str)[*offset] = '\0';
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_chrcpy_alloc                                                 *
 *                                                                            *
 * Purpose: Add zero character at the end of string.                          *
 *          Reallocs memory if not enough.                                    *
 *                                                                            *
 * Parameters: str - destination buffer pointer                               *
 *             alloc_len - already allocated memory                           *
 *             offset - offset for writing                                    *
 *             src - copied char                                              *
 *                                                                            *
 * Return value:                                                              *
 *                                                                            *
 * Author: Aleksander Vladishev                                               *
 *                                                                            *
 ******************************************************************************/
void	zbx_chrcpy_alloc(char **str, int *alloc_len, int *offset, const char src)
{
	assert(str);
	assert(offset && 0 <= *offset);
	assert(alloc_len && 0 <= *alloc_len);

	if (*offset + 1 >= *alloc_len) {
		*alloc_len += 64;
		*str = zbx_realloc(*str, *alloc_len);
	}

	(*str)[*offset] = src;
	(*offset)++;
	(*str)[*offset] = '\0';
}

/* Has to be rewritten to avoi malloc */
char *string_replace(char *str, char *sub_str1, char *sub_str2)
{
        char *new_str = NULL;
        char *p;
        char *q;
        char *r;
        char *t;
        long len;
        long diff;
        unsigned long count = 0;

	assert(str);
	assert(sub_str1);
	assert(sub_str2);

        len = (long)strlen(sub_str1);

        /* count the number of occurances of sub_str1 */
        for ( p=str; (p = strstr(p, sub_str1)); p+=len, count++ );

	if ( 0 == count )	return strdup(str);

        diff = (long)strlen(sub_str2) - len;

        /* allocate new memory */
        new_str = zbx_malloc(new_str, (size_t)(strlen(str) + count*diff + 1)*sizeof(char));

        for (q=str,t=new_str,p=str; (p = strstr(p, sub_str1)); )
        {
                /* copy until next occurance of sub_str1 */
                for ( ; q < p; *t++ = *q++);
                q += len;
                p = q;
                for ( r = sub_str2; (*t++ = *r++); );
                --t;
        }
        /* copy the tail of str */
        for( ; *q ; *t++ = *q++ );

	*t = '\0';

        return new_str;

}

/******************************************************************************
 *                                                                            *
 * Function: del_zeroes                                                       *
 *                                                                            *
 * Purpose: delete all right '0' and '.' for the string                       *
 *                                                                            *
 * Parameters: s - string to trim '0'                                         *
 *                                                                            *
 * Return value: string without right '0'                                     *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:  10.0100 => 10.01, 10. => 10                                                                 *
 *                                                                            *
 ******************************************************************************/
void del_zeroes(char *s)
{
	int     i;

	if(strchr(s,'.')!=NULL)
	{
		for(i = (int)strlen(s)-1;;i--)
		{
			if(s[i]=='0')
			{
				s[i]=0;
			}
			else if(s[i]=='.')
			{
				s[i]=0;
				break;
			}
			else
			{
				break;
			}
		}
	}
}

/******************************************************************************
 *                                                                            *
 * Function: delete_reol                                                      *
 *                                                                            *
 * Purpose: delete all right EOL characters                                   *
 *                                                                            *
 * Parameters: c - string to delete EOL                                       *
 *                                                                            *
 * Return value:  the string wtihout EOL                                      *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
void	delete_reol(char *c)
{
	int i;

	for(i=(int)strlen(c)-1;i>=0;i--)
	{
		if( c[i] != '\n')	break;
		c[i]=0;
	}
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_rtrim                                                        *
 *                                                                            *
 * Purpose: Strip characters from the end of a string                         *
 *                                                                            *
 * Parameters: str - string for processing                                    *
 *             charlist - null terminated list of characters                  *
 *                                                                            *
 * Return value: Stripped string                                              *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
void	zbx_rtrim(char *str, const char *charlist)
{
	register char *p;

	if( !str || !charlist || !*str || !*charlist ) return;

	for(
		p = str + strlen(str) - 1;
		p >= str && NULL != strchr(charlist,*p);
		p--)
			*p = '\0';
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_ltrim                                                        *
 *                                                                            *
 * Purpose: Strip characters from the beginning of a string                   *
 *                                                                            *
 * Parameters: str - string for processing                                    *
 *             charlist - null terminated list of characters                  *
 *                                                                            *
 * Return value: Stripped string                                              *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
void	zbx_ltrim(register char *str, const char *charlist)
{
	register char *p;

	if (NULL == str || NULL == charlist || '\0' == *str || '\0' == *charlist)
		return;

	for (p = str; '\0' != *p && NULL != strchr(charlist, *p); p++)
		;

	if (p == str)
		return;

	while ('\0' != *p)
		*str++ = *p++;

	*str = '\0';
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_remove_chars                                                 *
 *                                                                            *
 * Purpose: Remove characters 'charlist' from the whole string                *
 *                                                                            *
 * Parameters: str - string for processing                                    *
 *             charlist - null terminated list of characters                  *
 *                                                                            *
 * Return value:                                                              *
 *                                                                            *
 * Author: Aleksander Vladishev                                               *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
void	zbx_remove_chars(register char *str, const char *charlist)
{
	register char *p;

	if (NULL == str || NULL == charlist || '\0' == *str || '\0' == *charlist)
		return;

	for (p = str; '\0' != *p; p++)
		if (NULL == strchr(charlist, *p))
			*str++ = *p;

	*str = '\0';
}

/******************************************************************************
 *                                                                            *
 * Function: compress_signs                                                   *
 *                                                                            *
 * Purpose: convert all repeating pluses and minuses                          *
 *                                                                            *
 * Parameters: c - string to convert                                          *
 *                                                                            *
 * Return value: string without minuses                                       *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments: -3*--8+5-7*-4+++5 -> N3*8+5+N7*N4+5                              *
 *                                                                            *
 ******************************************************************************/
void	compress_signs(char *str)
{
	int	i,j,len;
	char	cur, next, prev;
	int	loop = 1;

/*	printf("In compress_signs [%s]\n", str);*/

	/* Compress '--' '+-' '++' '-+' */
	while(loop == 1)
	{
		loop=0;
		for(i=0;str[i]!='\0';i++)
		{
			cur=str[i];
			next=str[i+1];
			if(	(cur=='-' && next=='-') ||
				(cur=='+' && next=='+'))
			{
				str[i]='+';
				for(j=i+1;str[j]!='\0';j++)	str[j]=str[j+1];
				loop=1;
			}
			if(	(cur=='-' && next=='+') ||
				(cur=='+' && next=='-'))
			{
				str[i]='-';
				for(j=i+1;str[j]!='\0';j++)	str[j]=str[j+1];
				loop=1;
			}
		}
	}
/*	printf("After removing duplicates [%s]\n", str);*/

	/* Remove '-', '+' where needed, Convert -123 to +D123 */
	for(i=0;str[i]!='\0';i++)
	{
		cur=str[i];
		next=str[i+1];
		if(cur == '+')
		{
			/* Plus is the first sign in the expression */
			if(i==0)
			{
				for(j=i;str[j]!='\0';j++)	str[j]=str[j+1];
			}
			else
			{
				prev=str[i-1];
				if(!isdigit(prev) && prev!='.')
				{
					for(j=i;str[j]!='\0';j++)	str[j]=str[j+1];
				}
			}
		}
		else if(cur == '-')
		{
			/* Minus is the first sign in the expression */
			if(i==0)
			{
				str[i]='N';
			}
			else
			{
				prev=str[i-1];
				if(!isdigit(prev) && prev!='.')
				{
					str[i]='N';
				}
				else
				{
					len = (int)strlen(str);
					for(j=len;j>i;j--)	str[j]=str[j-1];
					str[i]='+';
					str[i+1]='N';
					str[len+1]='\0';
					i++;
				}
			}
		}
	}
/*	printf("After removing unnecessary + and - [%s]\n", str);*/
}


/******************************************************************************
 *                                                                            *
 * Function: rtrim_spaces                                                     *
 *                                                                            *
 * Purpose: delete all right spaces for the string                            *
 *                                                                            *
 * Parameters: c - string to trim spaces                                      *
 *                                                                            *
 * Return value: string without right spaces                                  *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
void	rtrim_spaces(char *c)
{
	int i,len;

	len = (int)strlen(c);
	for(i=len-1;i>=0;i--)
	{
		if( c[i] == ' ')
		{
			c[i]=0;
		}
		else	break;
	}
}

/******************************************************************************
 *                                                                            *
 * Function: ltrim_spaces                                                     *
 *                                                                            *
 * Purpose: delete all left spaces for the string                             *
 *                                                                            *
 * Parameters: c - string to trim spaces                                      *
 *                                                                            *
 * Return value: string without left spaces                                   *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
void	ltrim_spaces(char *c)
{
	int i;
/* Number of left spaces */
	int spaces=0;

	for(i=0;c[i]!=0;i++)
	{
		if( c[i] == ' ')
		{
			spaces++;
		}
		else	break;
	}
	for(i=0;c[i+spaces]!=0;i++)
	{
		c[i]=c[i+spaces];
	}

	c[strlen(c)-spaces]=0;
}

/******************************************************************************
 *                                                                            *
 * Function: lrtrim_spaces                                                    *
 *                                                                            *
 * Purpose: delete all left and right spaces for the string                   *
 *                                                                            *
 * Parameters: c - string to trim spaces                                      *
 *                                                                            *
 * Return value: string without left and right spaces                         *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
void	lrtrim_spaces(char *c)
{
	ltrim_spaces(c);
	rtrim_spaces(c);
}


/******************************************************************************
 *                                                                            *
 * Function: zbx_get_field                                                    *
 *                                                                            *
 * Purpose: return Nth field of characted separated string                    *
 *                                                                            *
 * Parameters: c - string to trim spaces                                      *
 *                                                                            *
 * Return value: string without left and right spaces                         *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
int	zbx_get_field(char *line, char *result, int num, char separator)
{
	int delim=0;
	int ptr=0;
	int i;

	int ret = FAIL;

	for(i=0;line[i]!=0;i++)
	{
		if(line[i]==separator)
		{
			delim++;
			continue;
		}
		if(delim==num)
		{
			result[ptr++]=line[i];
			result[ptr]=0;
			ret = SUCCEED;
		}
	}
	return ret;
}

/*
 * Function: strlcpy, strlcat
 * Copyright (c) 1998 Todd C. Miller <Todd.Miller@courtesan.com>
 *
 * Permission to use, copy, modify, and distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

/******************************************************************************
 *                                                                            *
 * Function: zbx_strlcpy                                                      *
 *                                                                            *
 * Purpose: replacement of insecure strncpy, same as OpenBSD's strlcpy        *
 *                                                                            *
 * Copy src to string dst of size siz.  At most siz-1 characters              *
 * will be copied.  Always NUL terminates (unless siz == 0).                  *
 * Returns strlen(src); if retval >= siz, truncation occurred.                *
 *                                                                            *
 * Author: Todd C. Miller <Todd.Miller@courtesan.com>                         *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
size_t zbx_strlcpy(char *dst, const char *src, size_t siz)
{
	char *d = dst;
	const char *s = src;
	size_t n = siz;

	/* Copy as many bytes as will fit */
	if (n != 0) {
		while (--n != 0) {
			if ((*d++ = *s++) == '\0')
				break;
		}
	}

	/* Not enough room in dst, add NUL and traverse rest of src */
	if (n == 0) {
		if (siz != 0)
			*d = '\0';                /* NUL-terminate dst */
		while (*s++)
		;
	}

	return(s - src - 1);        /* count does not include NUL */
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_strlcat                                                      *
 *                                                                            *
 * Purpose: replacement of insecure strncat, same as OpenBSD's strlcat        *
 *                                                                            *
 * Appends src to string dst of size siz (unlike strncat, size is the         *
 * full size of dst, not space left).  At most siz-1 characters               *
 * will be copied.  Always NUL terminates (unless siz <= strlen(dst)).        *
 * Returns strlen(src) + MIN(siz, strlen(initial dst)).                       *
 * If retval >= siz, truncation occurred.                                     *
 *                                                                            *
 * Author: Todd C. Miller <Todd.Miller@courtesan.com>                         *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 */
size_t zbx_strlcat(char *dst, const char *src, size_t siz)
{
	char *d = dst;
	const char *s = src;
	size_t n = siz;
	size_t dlen;

	/* Find the end of dst and adjust bytes left but don't go past end */
	while (n-- != 0 && *d != '\0')
		d++;
	dlen = d - dst;
	n = siz - dlen;

	if (n == 0)
		return(dlen + strlen(s));
	while (*s != '\0') {
		if (n != 1) {
			*d++ = *s;
			n--;
		}
		s++;
	}
	*d = '\0';

	return(dlen + (s - src));	/* count does not include NUL */
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_dvsprintf                                                     *
 *                                                                            *
 * Purpose: dinamical formatted output conversion                             *
 *                                                                            *
 * Return value: formated string                                              *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 * Comments:  required free allocated string with function 'zbx_free'         *
 *                                                                            *
 ******************************************************************************/
char* zbx_dvsprintf(char *dest, const char *f, va_list args)
{
	char	*string = NULL;
	int	n, size = MAX_STRING_LEN >> 1;

	va_list curr;

	while(1) {

		string = zbx_malloc(string, size);

		va_copy(curr, args);
		n = vsnprintf(string, size, f, curr);
		va_end(curr);

		if(n >= 0 && n < size)
			break;

		if(n >= size)	size = n + 1;
		else		size = size * 3 / 2 + 1;

		zbx_free(string);
	}

	if(dest) zbx_free(dest);

	return string;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_dsprintf                                                     *
 *                                                                            *
 * Purpose: dynamical formatted output conversion                             *
 *                                                                            *
 * Return value: formatted string                                             *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 * Comments:  required free allocated string with function 'zbx_free'         *
 *                                                                            *
 ******************************************************************************/
char* __zbx_zbx_dsprintf(char *dest, const char *f, ...)
{
	char	*string = NULL;
	va_list args;

	va_start(args, f);

	string = zbx_dvsprintf(dest, f, args);

	va_end(args);

	return string;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_strdcat                                                      *
 *                                                                            *
 * Purpose: dynamical cating of strings                                       *
 *                                                                            *
 * Return value: new pointer of string                                        *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 * Comments:  required free allocated string with function 'zbx_free'         *
 *            zbx_strdcat(NULL,"") must return "", not NULL!                  *
 *                                                                            *
 ******************************************************************************/
char* zbx_strdcat(char *dest, const char *src)
{
	register int new_len = 0;
	char *new_dest = NULL;

	if(!src)	return dest;
	if(!dest)	return strdup(src);

	new_len += (int)strlen(dest);
	new_len += (int)strlen(src);

	new_dest = zbx_malloc(new_dest, new_len + 1);

	if(dest)
	{
		strcpy(new_dest, dest);
		strcat(new_dest, src);
		zbx_free(dest);
	}
	else
	{
		strcpy(new_dest, src);
	}

	new_dest[new_len] = '\0';

	return new_dest;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_strdcat                                                      *
 *                                                                            *
 * Purpose: dynamical cating of formated strings                              *
 *                                                                            *
 * Return value: new pointer of string                                        *
 *                                                                            *
 * Author: Eugene Grigorjev                                                   *
 *                                                                            *
 * Comments:  required free allocated string with function 'zbx_free'         *
 *            zbx_strdcat(NULL,"") must return "", not NULL!                  *
 *                                                                            *
 ******************************************************************************/
char* __zbx_zbx_strdcatf(char *dest, const char *f, ...)
{
	char *string = NULL;
	char *result = NULL;

	va_list args;

	va_start(args, f);

	string = zbx_dvsprintf(NULL, f, args);

	va_end(args);

	result = zbx_strdcat(dest, string);

	zbx_free(string);

	return result;
}

/******************************************************************************
 *                                                                            *
 * Function: num_param                                                        *
 *                                                                            *
 * Purpose: calculate count of parameters from parameter list (param)         *
 *                                                                            *
 * Parameters:                                                                *
 * 	param  - parameter list                                               *
 *                                                                            *
 * Return value: count of parameters                                          *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:  delimeter for parameters is ','                                 *
 *                                                                            *
 ******************************************************************************/
int	num_param(const char *param)
{
	int	i;
	int	ret = 1;

/* 0 - init, 1 - inside quoted param, 2 - inside unquoted param */
	int	state = 0;
	char	c;

	if(param == NULL)
		return 0;

	for(i=0;param[i]!='\0';i++)
	{
		c=param[i];
		switch(state)
		{
			case 0:
				if(c==',')
				{
					ret++;
				}
				else if(c=='"')
				{
					state=1;
				}
				else if(c=='\\' && param[i+1]=='"')
				{
					state=2;
				}
				else if(c!=' ')
				{
					state=2;
				}
				break;
			case 1:
				if(c=='"')
				{
					state=0;
				}
				else if(c=='\\' && param[i+1]=='"')
				{
					i++;
					state=2;
				}
				break;
			case 2:
				if(c==',')
				{
					ret++;
					state=0;
				}
				break;
		}
	}

	return ret;
}

/******************************************************************************
 *                                                                            *
 * Function: get_param                                                        *
 *                                                                            *
 * Purpose: return parameter by index (num) from parameter list (param)       *
 *                                                                            *
 * Parameters:                                                                *
 * 	param  - parameter list                                               *
 *      num    - requested parameter index                                    *
 *      buf    - pointer of output buffer                                     *
 *      maxlen - size of output buffer                                        *
 *                                                                            *
 * Return value:                                                              *
 *      1 - requested parameter missing                                       *
 *      0 - requested parameter found (value - 'buf' can be empty string)     *
 *                                                                            *
 * Author: Eugene Grigorjev, rewritten by Alexei                              *
 *                                                                            *
 * Comments:  delimeter for parameters is ','                                 *
 *                                                                            *
 ******************************************************************************/
int	get_param(const char *param, int num, char *buf, int maxlen)
{
	int	ret = 1;
	int	i = 0;
	int	idx = 1;
	int	buf_i = 0;

/* 0 - init, 1 - inside quoted param, 2 - inside unquoted param */
	int	state = 0;
	char	c;

	buf[0]='\0';

	for(i=0; param[i] != '\0' && idx<=num && buf_i<maxlen; i++)
	{
		if(idx == num)	ret = 0;
		c=param[i];
		switch(state)
		{
			/* Init state */
			case 0:
				if(c==',')
				{
					idx++;
				}
				else if(c=='"')
				{
					state=1;
				}
				else if(idx == num)
				{
					if(c=='\\' && param[i+1]=='"')
					{
						buf[buf_i++]=c;
						i++;
						buf[buf_i++]=param[i];
					}
					else if(c!=' ')
					{
						buf[buf_i++]=c;
					}
					state=2;
				}
				break;
			/* Quoted */
			case 1:
				if(c=='"')
				{
					state=0;
				}
				else if(idx == num)
				{
					if(c=='\\' && param[i+1]=='"')
					{
						i++;
						buf[buf_i++]=param[i];
					}
					else
					{
						buf[buf_i++]=c;
					}
				}
				break;
			/* Unquoted */
			case 2:
				if(c==',')
				{
					idx++;
					state=0;
				}
				else if(idx == num)
				{
					buf[buf_i++]=c;
				}
				break;
		}
	}

	buf[buf_i]='\0';

	/* Missing first parameter will return OK */
	if(num == 1)
	{
		ret = 0;
	}

	return ret;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_num2hex                                                      *
 *                                                                            *
 * Purpose: convert parameter c (0-15) to hexadecimal value ('0'-'f')         *
 *                                                                            *
 * Parameters:                                                                *
 * 	c - number 0-15                                                       *
 *                                                                            *
 * Return value:                                                              *
 *      '0'-'f'                                                               *
 *                                                                            *
 * Author: Aleksander Vladishev                                               *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
char	zbx_num2hex(u_char c)
{
	if(c >= 10)
		return c + 0x57; /* a-f */
	else
		return c + 0x30; /* 0-9 */
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_hex2num                                                      *
 *                                                                            *
 * Purpose: convert hexit c ('0'-'9''a'-'f') to number (0-15)                 *
 *                                                                            *
 * Parameters:                                                                *
 * 	c - char ('0'-'9''a'-'f')                                             *
 *                                                                            *
 * Return value:                                                              *
 *      0-15                                                                  *
 *                                                                            *
 * Author: Aleksander Vladishev                                               *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
u_char	zbx_hex2num(char c)
{
	if(c >= 'a')
		return c - 0x57; /* a-f */
	else
		return c - 0x30; /* 0-9 */
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_binary2hex                                                   *
 *                                                                            *
 * Purpose: convert binary buffer input to hexadecimal string                 *
 *                                                                            *
 * Parameters:                                                                *
 * 	input - binary data                                                   *
 *	ilen - binary data length                                             *
 *	output - pointer to output buffer                                     *
 *	olen - output buffer length                                           *
 	*                                                                            *
 * Return value:                                                              *
 *                                                                            *
 * Author: Aleksander Vladishev                                               *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
int	zbx_binary2hex(const u_char *input, int ilen, char **output, int *olen)
{
	const u_char	*i = input;
	char		*o;
	int		len = (ilen * 2) + 1;

	assert(input);
	assert(output);
	assert(*output);
	assert(olen);

	if (*olen < len) {
		*olen = len;
		*output = zbx_realloc(*output, *olen);
	}
	o = *output;

	while (i - input < ilen) {
		*o++ = zbx_num2hex( (*i >> 4) & 0xf );
		*o++ = zbx_num2hex( *i & 0xf );
		i++;
	}
	*o = '\0';

	return len - 1;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_hex2binary                                                   *
 *                                                                            *
 * Purpose: convert hexadecimal string to binary buffer                       *
 *                                                                            *
 * Parameters:                                                                *
 * 	io - hexadecimal string                                               *
 *                                                                            *
 * Return value:                                                              *
 *	size of buffer                                                        *
 *                                                                            *
 * Author: Aleksander Vladishev                                               *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
int	zbx_hex2binary(char *io)
{
	const char	*i = io;
	char		*o = io;
	u_char		c;

	assert(io);

	while(*i != '\0') {
		c = zbx_hex2num( *i++ ) << 4;
		c += zbx_hex2num( *i++ );
		*o++ = (char)c;
	}
	*o = '\0';

	return (int)(o - io);
}

#ifdef HAVE_POSTGRESQL
/******************************************************************************
 *                                                                            *
 * Function: zbx_pg_escape_bytea                                              *
 *                                                                            *
 * Purpose: converts from binary string to the null terminated escaped string *
 *                                                                            *
 * Transformations:                                                           *
 *	'\0' [0x00] -> \\ooo (ooo is an octal number)                         *
 *	'\'' [0x37] -> \'                                                     *
 *	'\\' [0x5c] -> \\\\                                                   *
 *	<= 0x1f || >= 0x7f -> \\ooo                                           *
 *                                                                            *
 * Parameters:                                                                *
 *	input - null terminated hexadecimal string                            *
 *	output - pointer to buffer                                            *
 *	olen - size of returned buffer                                        *
 *                                                                            *
 * Return value:                                                              *
 *                                                                            *
 * Author: Aleksander Vladishev                                               *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
int	zbx_pg_escape_bytea(const u_char *input, int ilen, char **output, int *olen)
{
	const u_char	*i;
	char		*o;
	int		len;

	assert(input);
	assert(output);
	assert(*output);
	assert(olen);

	len = 1; /* '\0' */
	i = input;
	while(i - input < ilen)
	{
		if(*i == '\0' || *i <= 0x1f || *i >= 0x7f)
			len += 5;
		else if(*i == '\'')
			len += 2;
		else if(*i == '\\')
			len += 4;
		else
			len++;
		i++;
	}

	if(*olen < len)
	{
		*olen = len;
		*output = zbx_realloc(*output, *olen);
	}
	o = *output;
	i = input;

	while(i - input < ilen) {
		if(*i == '\0' || *i <= 0x1f || *i >= 0x7f)
		{
			*o++ = '\\';
			*o++ = '\\';
			*o++ = ((*i >> 6) & 0x7) + 0x30;
			*o++ = ((*i >> 3) & 0x7) + 0x30;
			*o++ = (*i & 0x7) + 0x30;
		}
		else if (*i == '\'')
		{
			*o++ = '\\';
			*o++ = '\'';
		}
		else if (*i == '\\')
		{
			*o++ = '\\';
			*o++ = '\\';
			*o++ = '\\';
			*o++ = '\\';
		}
		else
			*o++ = *i;
		i++;
	}
	*o = '\0';

	return len - 1;
}

/******************************************************************************
 *                                                                            *
 * Function: zbx_pg_unescape_bytea                                            *
 *                                                                            *
 * Purpose: converts the null terminated string into binary buffer            *
 *                                                                            *
 * Transformations:                                                           *
 *	\ooo == a byte whose value = ooo (ooo is an octal number)             *
 *	\x   == x (x is any character)                                        *
 *                                                                            *
 * Parameters:                                                                *
 *	io - null terminated string                                           *
 *                                                                            *
 * Return value: length of the binary buffer                                  *
 *                                                                            *
 * Author: Aleksander Vladishev                                               *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
int	zbx_pg_unescape_bytea(u_char *io)
{
	const u_char	*i = io;
	u_char		*o = io;

	assert(io);

	while(*i != '\0') {
		switch(*i)
		{
			case '\\':
				i++;
				if(*i == '\\')
				{
					*o++ = *i++;
				}
				else
				{
					if(*i >= 0x30 && *i <= 0x39 && *(i + 1) >= 0x30 && *(i + 1) <= 0x39 && *(i + 2) >= 0x30 && *(i + 2) <= 0x39)
					{
						*o = (*i++ - 0x30) << 6;
						*o += (*i++ - 0x30) << 3;
						*o++ += *i++ - 0x30;
					}
				}
				break;

			default:
				*o++ = *i++;
		}
	}

	return o - io;
}
#endif
/******************************************************************************
 *                                                                            *
 * Function: zbx_get_next_field                                               *
 *                                                                            *
 * Purpose: return current field of characted separated string                *
 *                                                                            *
 * Parameters:                                                                *
 *	line - null terminated, character separated string                    *
 *	output - output buffer (current field)                                *
 *	olen - allocated output buffer size                                   *
 *	separator - fields separator                                          *
 *                                                                            *
 * Return value: pointer to the next field                                    *
 *                                                                            *
 * Author: Aleksander Vladishev                                               *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
int	zbx_get_next_field(const char **line, char **output, int *olen, char separator)
{
	char	*ret;
	int	flen;

	assert(line);

	if (NULL == *line)
	{
		(*output)[0] = '\0';
		return 0;
	}

	ret = strchr(*line, separator);
	if (ret) {
		flen = (int)(ret - *line);
		ret++;
	} else
		flen = (int)strlen(*line);

	if (*olen < flen + 1) {
		*olen = flen * 2;
		*output = zbx_realloc(*output, *olen);
	}
	memcpy(*output, *line, flen);
	(*output)[flen] = '\0';

	*line = ret;

	return flen;
}

/******************************************************************************
 *                                                                            *
 * Function: str_in_list                                                      *
 *                                                                            *
 * Purpose: check if string matches a list of delimited strings               *
 *                                                                            *
 * Parameters: list     - strings a,b,ccc,ddd                                 *
 *             value    - value                                               *
 *             delimiter- delimiter                                           *
 *                                                                            *
 * Return value: FAIL - out of period, SUCCEED - within the period            *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:                                                                  *
 *                                                                            *
 ******************************************************************************/
int	str_in_list(char *list, const char *value, const char delimiter)
{
	char	*start, *end;
	int	ret = FAIL;

	for (start = list; *start != '\0' && ret == FAIL;) {
		if (NULL != (end = strchr(start, delimiter)))
			*end = '\0';

		if (0 == strcmp(start, value))
			ret = SUCCEED;

		if (end != NULL) {
			*end = delimiter;
			start = end + 1;
		} else
			break;
	}
	return ret;
}

/******************************************************************************
 *                                                                            *
 * Function: get_key_param                                                    *
 *                                                                            *
 * Purpose: return parameter by index (num) from parameter list (param)       *
 *          to be used for keys: key[param1,param2]                           *
 *                                                                            *
 * Parameters:                                                                *
 * 	param  - parameter list                                               *
 *      num    - requested parameter index                                    *
 *      buf    - pointer of output buffer                                     *
 *      maxlen - size of output buffer                                        *
 *                                                                            *
 * Return value:                                                              *
 *      1 - requested parameter missing                                        *
 *      0 - requested parameter found (value - 'buf' can be empty string)     *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:  delimeter for parameters is ','                                 *
 *                                                                            *
 ******************************************************************************/
int	get_key_param(char *param, int num, char *buf, int maxlen)
{
	int	ret = 0;

	char *pl, *pr;

	pl = strchr(param, '[');
	pr = strrchr(param, ']');

	if(pl > pr)
		return 1;

	if(!pl || !pr || (pl && !pr) || (!pl && pr))
		return 1;

	if(pr != NULL)
		pr[0] = 0;

	ret = get_param(pl+1, num, buf, maxlen);

	if(pr != NULL)
		pr[0]=']';

	return ret;
}

/******************************************************************************
 *                                                                            *
 * Function: num_key_param                                                    *
 *                                                                            *
 * Purpose: calculate count of parameters from parameter list (param)         *
 *          to be used for keys: key[param1,param2]                           *
 *                                                                            *
 * Parameters:                                                                *
 * 	param  - parameter list                                               *
 *                                                                            *
 * Return value: count of parameters                                          *
 *                                                                            *
 * Author: Alexei Vladishev                                                   *
 *                                                                            *
 * Comments:  delimeter for parameters is ','                                 *
 *                                                                            *
 ******************************************************************************/
int	num_key_param(char *param)
{
	int	ret = 1;

	char *pl, *pr;

	if(param == NULL)
		return 0;

	pl = strchr(param, '[');
	pr = strrchr(param, ']');

	if(pl > pr)
		return 0;

	if(!pl || !pr || (pl && !pr) || (!pl && pr))
		return 0;

	if(pl != NULL)
		pl[0] = 0;
	if(pr != NULL)
		pr[0] = 0;

	ret = num_param(pl+1);

	if(pl != NULL)
		pl[0]='[';
	if(pr != NULL)
		pr[0]=']';

	return ret;
}

char	*zbx_age2str(int age)
{
	int		days, hours, minutes, offset;
	static char	buffer[32];

	days	= (int)((double)age / 86400);
	hours	= (int)((double)(age - days * 86400) / 3600);
	minutes	= (int)((double)(age - days * 86400 - hours * 3600) / 60);
	offset	= 0;

	if (days)
		offset += zbx_snprintf(buffer + offset, sizeof(buffer) - offset, "%dd ", days);
	if (days || hours)
		offset += zbx_snprintf(buffer + offset, sizeof(buffer) - offset, "%dh ", hours);
	offset += zbx_snprintf(buffer + offset, sizeof(buffer) - offset, "%dm", minutes);

	return buffer;
}

char	*zbx_date2str(time_t date)
{
	static char	buffer[11];
	struct tm	*tm;

	tm	= localtime(&date);
	zbx_snprintf(buffer, sizeof(buffer), "%.4d.%.2d.%.2d",
			tm->tm_year + 1900,
			tm->tm_mon + 1,
			tm->tm_mday);

	return buffer;
}

char	*zbx_time2str(time_t time)
{
	static char	buffer[9];
	struct tm	*tm;

	tm	= localtime(&time);
	zbx_snprintf(buffer, sizeof(buffer), "%.2d:%.2d:%.2d",
			tm->tm_hour,
			tm->tm_min,
			tm->tm_sec);
	return buffer;
}

static int	zbx_strncasecmp(const char *s1, const char *s2, size_t n)
{
	if (NULL == s1 && NULL == s2)
		return 0;

	if (NULL == s1)
		return 1;

	if (NULL == s2)
		return -1;

	while (n && '\0' != *s1 && '\0' != *s2 &&
			tolower((unsigned char)*s1) == tolower((unsigned char)*s2))
	{
		s1++;
		s2++;
		n--;
	}

	return n == 0 ? 0 : tolower((unsigned char)*s1) - tolower((unsigned char)*s2);
}

char	*zbx_strcasestr(const char *haystack, const char *needle)
{
/*#ifdef HAVE_STRCASESTR
	return strcasestr(haystack, needle);
#else*/
	size_t		sz_h, sz_n;
	const char	*p;

	if (NULL == needle || '\0' == *needle)
		return (char *)haystack;

	if (NULL == haystack || '\0' == *haystack)
		return NULL;

	sz_h = strlen(haystack);
	sz_n = strlen(needle);
	if (sz_h < sz_n)
		return NULL;

	for (p = haystack; p <= &haystack[sz_h - sz_n]; p++)
	{
		if (0 == zbx_strncasecmp(p, needle, sz_n))
			return (char *)p;
	}

	return NULL;
/*#endif*/
}

const char *zbx_permission_string(int perm)
{
	switch (perm) {
	case PERM_DENY:
		return "dn";
	case PERM_READ_LIST:
		return "rl";
	case PERM_READ_ONLY:
		return "ro";
	case PERM_READ_WRITE:
		return "rw";
	default:
		return "unknown";
	}
}

char	*zbx_item_value_type_string(zbx_item_value_type_t value_type)
{
	switch (value_type) {
	case ITEM_VALUE_TYPE_FLOAT: return "Numeric (float)";
	case ITEM_VALUE_TYPE_STR: return "Character";
	case ITEM_VALUE_TYPE_LOG: return "Log";
	case ITEM_VALUE_TYPE_UINT64: return "Numeric (integer 64bit)";
	case ITEM_VALUE_TYPE_TEXT: return "Text";
	default: return "unknown";
	}
}

char	*zbx_result_string(int result)
{
	switch (result) {
	case SUCCEED: return "SUCCEED";
	case FAIL: return "FAIL";
	case NOTSUPPORTED: return "NOTSUPPORTED";
	case NETWORK_ERROR: return "NETWORK_ERROR";
	case TIMEOUT_ERROR: return "TIMEOUT_ERROR";
	case AGENT_ERROR: return "AGENT_ERROR";
	default: return "unknown";
	}
}

char	*zbx_trigger_severity_string(zbx_trigger_severity_t severity)
{
	switch (severity) {
	case TRIGGER_SEVERITY_NOT_CLASSIFIED: return "Not classified";
	case TRIGGER_SEVERITY_INFORMATION: return "Information";
	case TRIGGER_SEVERITY_WARNING: return "Warning";
	case TRIGGER_SEVERITY_AVERAGE: return "Average";
	case TRIGGER_SEVERITY_HIGH: return "High";
	case TRIGGER_SEVERITY_DISASTER: return "Disaster";
	default: return "unknown";
	}
}

char	*zbx_dservice_type_string(zbx_dservice_type_t service)
{
	switch (service) {
	case SVC_SSH: return "SSH";
	case SVC_LDAP: return "LDAP";
	case SVC_SMTP: return "SMTP";
	case SVC_FTP: return "FTP";
	case SVC_HTTP: return "HTTP";
	case SVC_POP: return "POP";
	case SVC_NNTP: return "NNTP";
	case SVC_IMAP: return "IMAP";
	case SVC_TCP: return "TCP";
	case SVC_AGENT: return "ZABBIX agent";
	case SVC_SNMPv1: return "SNMPv1 agent";
	case SVC_SNMPv2c: return "SNMPv2c agent";
	case SVC_SNMPv3: return "SNMPv3 agent";
	case SVC_ICMPPING: return "ICMP Ping";
	default: return "unknown";
	}
}

#ifdef _WINDOWS
static int	get_codepage(const char *encoding, unsigned int *codepage)
{
	typedef struct codepage_s {
		unsigned int	codepage;
		char		*name;
	} codepage_t;

	int		i;
	char		buf[16];
	codepage_t	cp[] = {{0, "ANSI"}, {037, "IBM037"}, {437, "IBM437"}, {500, "IBM500"}, {708, "ASMO-708"},
			{709, NULL}, {710, NULL}, {720, "DOS-720"}, {737, "IBM737"}, {775, "IBM775"}, {850, "IBM850"},
			{852, "IBM852"}, {855, "IBM855"}, {857, "IBM857"}, {858, "IBM00858"}, {860, "IBM860"},
			{861, "IBM861"}, {862, "DOS-862"}, {863, "IBM863"}, {864, "IBM864"}, {865, "IBM865"},
			{866, "CP866"}, {869, "IBM869"}, {870, "IBM870"}, {874, "WINDOWS-874"}, {875, "CP875"},
			{932, "SHIFT_JIS"}, {936, "GB2312"}, {949, "KS_C_5601-1987"}, {950, "BIG5"}, {1026, "IBM1026"},
			{1047, "IBM01047"}, {1140, "IBM01140"}, {1141, "IBM01141"}, {1142, "IBM01142"},
			{1143, "IBM01143"}, {1144, "IBM01144"}, {1145, "IBM01145"}, {1146, "IBM01146"},
			{1147, "IBM01147"}, {1148, "IBM01148"}, {1149, "IBM01149"}, {1200, "UTF-16"},
			{1201, "UNICODEFFFE"}, {1250, "WINDOWS-1250"}, {1251, "WINDOWS-1251"}, {1252, "WINDOWS-1252"},
			{1253, "WINDOWS-1253"}, {1254, "WINDOWS-1254"}, {1255, "WINDOWS-1255"}, {1256, "WINDOWS-1256"},
			{1257, "WINDOWS-1257"}, {1258, "WINDOWS-1258"}, {1361, "JOHAB"}, {10000, "MACINTOSH"},
			{10001, "X-MAC-JAPANESE"}, {10002, "X-MAC-CHINESETRAD"}, {10003, "X-MAC-KOREAN"},
			{10004, "X-MAC-ARABIC"}, {10005, "X-MAC-HEBREW"}, {10006, "X-MAC-GREEK"},
			{10007, "X-MAC-CYRILLIC"}, {10008, "X-MAC-CHINESESIMP"}, {10010, "X-MAC-ROMANIAN"},
			{10017, "X-MAC-UKRAINIAN"}, {10021, "X-MAC-THAI"}, {10029, "X-MAC-CE"},
			{10079, "X-MAC-ICELANDIC"}, {10081, "X-MAC-TURKISH"}, {10082, "X-MAC-CROATIAN"},
			{12000, "UTF-32"}, {12001, "UTF-32BE"}, {20000, "X-CHINESE_CNS"}, {20001, "X-CP20001"},
			{20002, "X_CHINESE-ETEN"}, {20003, "X-CP20003"}, {20004, "X-CP20004"}, {20005, "X-CP20005"},
			{20105, "X-IA5"}, {20106, "X-IA5-GERMAN"}, {20107, "X-IA5-SWEDISH"}, {20108, "X-IA5-NORWEGIAN"},
			{20127, "US-ASCII"}, {20261, "X-CP20261"}, {20269, "X-CP20269"}, {20273, "IBM273"},
			{20277, "IBM277"}, {20278, "IBM278"}, {20280, "IBM280"}, {20284, "IBM284"}, {20285, "IBM285"},
			{20290, "IBM290"}, {20297, "IBM297"}, {20420, "IBM420"}, {20423, "IBM423"}, {20424, "IBM424"},
			{20833, "X-EBCDIC-KOREANEXTENDED"}, {20838, "IBM-THAI"}, {20866, "KOI8-R"}, {20871, "IBM871"},
			{20880, "IBM880"}, {20905, "IBM905"}, {20924, "IBM00924"}, {20932, "EUC-JP"},
			{20936, "X-CP20936"}, {20949, "X-CP20949"}, {21025, "CP1025"}, {21027, NULL}, {21866, "KOI8-U"},
			{28591, "ISO-8859-1"}, {28592, "ISO-8859-2"}, {28593, "ISO-8859-3"}, {28594, "ISO-8859-4"},
			{28595, "ISO-8859-5"}, {28596, "ISO-8859-6"}, {28597, "ISO-8859-7"}, {28598, "ISO-8859-8"},
			{28599, "ISO-8859-9"}, {28603, "ISO-8859-13"}, {28605, "ISO-8859-15"}, {29001, "X-EUROPA"},
			{38598, "ISO-8859-8-I"}, {50220, "ISO-2022-JP"}, {50221, "CSISO2022JP"}, {50222, "ISO-2022-JP"},
			{50225, "ISO-2022-KR"}, {50227, "X-CP50227"}, {50229, NULL}, {50930, NULL}, {50931, NULL},
			{50933, NULL}, {50935, NULL}, {50936, NULL}, {50937, NULL}, {50939, NULL}, {51932, "EUC-JP"},
			{51936, "EUC-CN"}, {51949, "EUC-KR"}, {51950, NULL}, {52936, "HZ-GB-2312"}, {54936, "GB18030"},
			{57002, "X-ISCII-DE"}, {57003, "X-ISCII-BE"}, {57004, "X-ISCII-TA"}, {57005, "X-ISCII-TE"},
			{57006, "X-ISCII-AS"}, {57007, "X-ISCII-OR"}, {57008, "X-ISCII-KA"}, {57009, "X-ISCII-MA"},
			{57010, "X-ISCII-GU"}, {57011, "X-ISCII-PA"}, {65000, "UTF-7"}, {65001, "UTF-8"}, {0, NULL}};

	if ('\0' == *encoding)
	{
		*codepage = 0;	/* ANSI */
		return SUCCEED;
	}

	/* by name */
	for (i = 0; 0 != cp[i].codepage || NULL != cp[i].name; i++)
	{
		if (NULL == cp[i].name)
			continue;

		if (0 == strcmp(encoding, cp[i].name))
		{
			*codepage = cp[i].codepage;
			return SUCCEED;
		}
	}

	/* by number */
	for (i = 0; 0 != cp[i].codepage || NULL != cp[i].name; i++)
	{
		_itoa_s(cp[i].codepage, buf, sizeof(buf), 10);
		if (0 == strcmp(encoding, buf))
		{
			*codepage = cp[i].codepage;
			return SUCCEED;
		}
	}

	/* by 'cp' + number */
	for (i = 0; 0 != cp[i].codepage || NULL != cp[i].name; i++)
	{
		zbx_snprintf(buf, sizeof(buf), "cp%li", cp[i].codepage);
		if (0 == strcmp(encoding, buf))
		{
			*codepage = cp[i].codepage;
			return SUCCEED;
		}
	}

	return FAIL;
}

/* convert from selected code page to unicode */
static LPTSTR	zbx_to_unicode(unsigned int codepage, LPCSTR cp_string)
{
	LPTSTR	wide_string = NULL;
	int	wide_size;

	wide_size = MultiByteToWideChar(codepage, 0, cp_string, -1, NULL, 0);
	wide_string = (LPTSTR)zbx_malloc(wide_string, (size_t)wide_size * sizeof(TCHAR));

	/* convert from cp_string to wide_string */
	MultiByteToWideChar(codepage, 0, cp_string, -1, wide_string, wide_size);

	return wide_string;
}

/* convert from Windows ANSI code page to unicode */
LPTSTR	zbx_acp_to_unicode(LPCSTR acp_string)
{
	return zbx_to_unicode(CP_ACP, acp_string);
}

int	zbx_acp_to_unicode_static(LPCSTR acp_string, LPTSTR wide_string, int wide_size)
{
	/* convert from acp_string to wide_string */
	if (0 == MultiByteToWideChar(CP_ACP, 0, acp_string, -1, wide_string, wide_size))
		return FAIL;

	return SUCCEED;
}

/* convert from UTF-8 to unicode */
LPTSTR	zbx_utf8_to_unicode(LPCSTR utf8_string)
{
	return zbx_to_unicode(CP_UTF8, utf8_string);
}

/* convert from unicode to utf8 */
LPSTR	zbx_unicode_to_utf8(LPCTSTR wide_string)
{
	LPSTR	utf8_string = NULL;
	int	utf8_size;

	utf8_size = WideCharToMultiByte(CP_UTF8, 0, wide_string, -1, NULL, 0, NULL, NULL);
	utf8_string = (LPSTR)zbx_malloc(utf8_string, (size_t)utf8_size);

	/* convert from wide_string to utf8_string */
	WideCharToMultiByte(CP_UTF8, 0, wide_string, -1, utf8_string, utf8_size, NULL, NULL);

	return utf8_string;
}

/* convert from unicode to utf8 */
int	zbx_unicode_to_utf8_static(LPCTSTR wide_string, LPSTR utf8_string, int utf8_size)
{
	/* convert from wide_string to utf8_string */
	if (0 == WideCharToMultiByte(CP_UTF8, 0, wide_string, -1, utf8_string, utf8_size, NULL, NULL))
		return FAIL;

	return SUCCEED;
}
#endif

void	zbx_strupper(char *str)
{
	for (; '\0' != *str; str++)
		*str = toupper((int)*str);
}

#if defined(_WINDOWS)
#include "log.h"
char	*convert_to_utf8(char *in, size_t in_size, const char *encoding)
{
#define STATIC_SIZE	1024
	wchar_t	wide_string_static[STATIC_SIZE], *wide_string = NULL;
	int		wide_size;
	char		*utf8_string = NULL;
	int		utf8_size;
	unsigned int	codepage;

	if (FAIL == get_codepage(encoding, &codepage))
	{
		utf8_size = in_size + 1;
		utf8_string = zbx_malloc(utf8_string, utf8_size);
		memcpy(utf8_string, in, in_size);
		utf8_string[in_size] = '\0';
		return utf8_string;
	}

	zabbix_log(LOG_LEVEL_DEBUG, "convert_to_utf8() in_size:%d encoding:'%s' codepage:%u", in_size, encoding, codepage);

	if (1200 != codepage)	/* UTF-16 */
	{
		wide_size = MultiByteToWideChar(codepage, 0, in, in_size, NULL, 0);
		if (wide_size > STATIC_SIZE)
			wide_string = (LPTSTR)zbx_malloc(wide_string, (size_t)wide_size * sizeof(TCHAR));
		else
			wide_string = wide_string_static;

		/* convert from in to wide_string */
		MultiByteToWideChar(codepage, 0, in, in_size, wide_string, wide_size);
	}
	else
	{
		wide_string = (wchar_t *)in;
		wide_size = in_size / 2;
	}

	utf8_size = WideCharToMultiByte(CP_UTF8, 0, wide_string, wide_size, NULL, 0, NULL, NULL);
	utf8_string = (LPSTR)zbx_malloc(utf8_string, (size_t)utf8_size + 1/* '\0' */);

	/* convert from wide_string to utf8_string */
	WideCharToMultiByte(CP_UTF8, 0, wide_string, wide_size, utf8_string, utf8_size, NULL, NULL);
	utf8_string[utf8_size] = '\0';
	zabbix_log(LOG_LEVEL_DEBUG, "convert_to_utf8() utf8_size:%d utf8_string:'%s'", utf8_size, utf8_string);

	if (wide_string != wide_string_static && wide_string != (wchar_t *)in)
		zbx_free(wide_string);

	return utf8_string;
}
#elif defined(HAVE_ICONV)
char	*convert_to_utf8(char *in, size_t in_size, const char *encoding)
{
	iconv_t		cd;
	size_t		in_size_left, out_size_left, sz, out_alloc = 0;
	const char	to_code[] = "UTF-8";
	char		*out = NULL, *p;

	out_alloc = in_size + 1;
	p = out = zbx_malloc(out, out_alloc);

	if (*encoding == '\0' || (iconv_t)-1 == (cd = iconv_open(to_code, encoding)))
	{
		memcpy(out, in, in_size);
		out[in_size] = '\0';
		return out;
	}

	in_size_left = in_size;
	out_size_left = out_alloc - 1;

	while ((size_t)(-1) == iconv(cd, &in, &in_size_left, &p, &out_size_left))
	{
		if (E2BIG != errno)
			break;

		sz = (size_t)(p - out);
		out_alloc += in_size;
		out_size_left += in_size;
		p = out = zbx_realloc(out, out_alloc);
		p += sz;
	}

	*p = '\0';

	iconv_close(cd);

	return out;
}
#endif	/* HAVE_ICONV */

void	win2unix_eol(char *text)
{
	size_t	i, sz;

	sz = strlen(text);

	for (i = 0; i < sz; i++)
	{
		if (text[i] == '\r' && text[i + 1] == '\n')	/* CR+LF (Windows) */
		{
			text[i] = '\n';	/* LF (Unix) */
			sz--;
			memmove(&text[i + 1], &text[i + 2], (sz - i) * sizeof(char));
		}
	}
}